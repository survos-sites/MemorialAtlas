<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\{Image, Item};
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\AI\Store\Document\{Metadata, TextDocument, VectorDocumentInterface, VectorizerInterface};
use Symfony\AI\Store\Query\VectorQuery;
use Symfony\AI\Store\StoreInterface;
use Symfony\Component\Console\Attribute\{AsCommand, Option};
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;

/**
 * One embedding per stone, made from its dense summary and kept in pgvector, so the chat can find the
 * stones that match a question without reading every summary.
 */
final class MemorialVectors
{
    /** The table of the "memorials" store in config/packages/ai.yaml. */
    public const TABLE = 'memorial_vectors';
    private const UUID_NAMESPACE = 'b6f6a3c2-6f0e-4d38-9a51-5d1f2e7c4a90';

    public function __construct(
        private EntityManagerInterface $em,
        private Connection $connection,
        #[Autowire(service: 'ai.vectorizer.huggingface')] private VectorizerInterface $vectorizer,
        #[Autowire(service: 'ai.store.postgres.memorials')] private StoreInterface $store,
    ) {}

    /** Words a question uses for each symbol category, so "hobbies" or "veterans" lands near "crossed lacrosse sticks" or "U.S. Army emblem". */
    private const THEMES = ['music' => 'music, a hobby or interest', 'sport' => 'sport, a hobby or interest', 'hobby' => 'a hobby or interest',
        'work' => 'work or profession', 'military' => 'military service, a veteran', 'faith' => 'faith or religion', 'nature' => 'nature'];

    /** The dense summary, followed by the stored symbols with their category and meaning. */
    public static function text(Item $item): string
    {
        $symbols = [];
        foreach ($item->media as $media) {
            if (!$media instanceof Image) continue;
            foreach ($media->aiResults[SymbolFinder::RESULT]['items'] ?? [] as $symbol) {
                $theme = self::THEMES[$symbol['category'] ?? ''] ?? null;
                $symbols[$symbol['name']] = sprintf('%s (%s)', $symbol['name'], implode('; ', array_filter([$theme, $symbol['meaning'] ?? null])));
            }
        }

        return ($item->denseSummary ?? $item->title).($symbols === [] ? '' : "\nCarved symbols: ".implode(', ', $symbols).'.');
    }

    /**
     * Uses a pure vector query: the store's hybrid query only returns rows that also match the words of
     * the question, so "hobbies" would miss a stone that shows a guitar.
     *
     * @return list<array{id: int, title: string, summary: string, distance: float}>
     */
    public function find(string $question, int $limit = 8): array
    {
        $hits = [];
        foreach ($this->store->query(new VectorQuery($this->vectorizer->vectorize($question)), ['limit' => $limit]) as $document) {
            /** @var VectorDocumentInterface $document */
            $metadata = $document->getMetadata();
            $hits[] = ['id' => (int) $metadata['itemId'], 'title' => (string) $metadata['title'], 'summary' => (string) $metadata->getText(), 'distance' => round((float) $document->getScore(), 3)];
        }

        return $hits;
    }

    /** @return array{added: int, kept: int, removed: int} */
    public function index(bool $refresh = false): array
    {
        $items = $this->em->getRepository(Item::class)->findBy(['marking' => ['review', 'complete']], ['id' => 'ASC']);
        // Text hashes already stored: an unchanged summary keeps its vector, so a reload costs no embedding call.
        $stored = $this->connection->fetchAllKeyValue(sprintf("SELECT id::text, metadata->>'textHash' FROM %s", self::TABLE));

        $documents = [];
        $wanted = [];
        foreach ($items as $item) {
            $id = Uuid::v5(Uuid::fromString(self::UUID_NAMESPACE), 'item-'.$item->id)->toRfc4122();
            $wanted[$id] = true;
            $text = self::text($item);
            $hash = hash('sha256', $text);
            if (!$refresh && ($stored[$id] ?? null) === $hash) continue;
            $documents[] = new TextDocument($id, $text, new Metadata(['itemId' => $item->id, 'title' => $item->title, 'textHash' => $hash, Metadata::KEY_TEXT => $text]));
        }

        // mistral-embed takes a batch of texts per call.
        foreach (array_chunk($documents, 64) as $chunk) $this->store->add($this->vectorizer->vectorize($chunk));
        $gone = array_keys(array_diff_key($stored, $wanted));
        if ($gone !== []) $this->store->remove($gone);

        return ['added' => count($documents), 'kept' => count($items) - count($documents), 'removed' => count($gone)];
    }

    #[AsCommand('app:vectorize', 'Store one embedding per stone (from its dense summary) for semantic search in the chat')]
    public function __invoke(SymfonyStyle $io, #[Option('Embed every record again, even when its summary did not change')] bool $refresh = false,
        #[Option('Only show the stones nearest to this question')] ?string $find = null): int
    {
        if ($find !== null) {
            foreach ($this->find($find, 15) as $hit) $io->text(sprintf('%.3f  <info>%d</info> %s', $hit['distance'], $hit['id'], $hit['title']));

            return 0;
        }
        $result = $this->index($refresh);
        $io->success(sprintf('%d embedded, %d unchanged, %d removed.', $result['added'], $result['kept'], $result['removed']));

        return 0;
    }
}
