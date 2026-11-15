<?php

declare(strict_types=1);

namespace App\Mcp;

use App\Decision\ImageQuestions as Q;
use App\Entity\{Image, Item};
use App\Image\ImagePreset;
use App\Service\{ImageCleaner, ImageDelivery, MemorialVectors, Remembrance, SymbolFinder};
use Doctrine\ORM\EntityManagerInterface;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Schema\ToolAnnotations;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Read-only MCP tools over the memorial collection. Every answer separates what the AI proposed from
 * what is verified: nothing here is a verified burial record, and AI-cleaned photos are display copies.
 */
final readonly class MemorialTools
{
    public function __construct(
        private EntityManagerInterface $em,
        private ImageDelivery $images,
        private UrlGeneratorInterface $urls,
        private MemorialVectors $vectors,
    ) {}

    /**
     * Find the stones whose dense summary is closest in meaning to a question, for example "veterans",
     * "music or musical instruments" or "stones with scripture". Returns each stone's summary, nearest first.
     *
     * @param string $question What to look for, in plain words
     * @param int    $limit    Maximum number of stones (1 to 30)
     *
     * @return list<array<string, mixed>>
     */
    #[McpTool(name: 'find_memorials', annotations: new ToolAnnotations(title: 'Find memorials by meaning', readOnlyHint: true, openWorldHint: false))]
    public function find(string $question, int $limit = 10): array
    {
        return array_map(fn (array $hit): array => $hit + ['url' => $this->urls->generate('item_show', ['id' => $hit['id']], UrlGeneratorInterface::ABSOLUTE_URL)],
            $this->vectors->find($question, max(1, min(30, $limit))));
    }

    /**
     * Search memorial records by name, inscription text, description or a symbol on the stone (for example guitar, hockey, army).
     *
     * @param string $query Words to look for, for example a surname or a year
     * @param int    $limit Maximum number of records (1 to 50)
     *
     * @return list<array<string, mixed>>
     */
    #[McpTool(name: 'search_memorials', annotations: new ToolAnnotations(title: 'Search memorials', readOnlyHint: true, openWorldHint: false))]
    public function search(string $query, int $limit = 10): array
    {
        $ids = $this->em->getConnection()->fetchFirstColumn(
            'SELECT i.id FROM item i LEFT JOIN media m ON m.item_id = i.id
             WHERE i.title ILIKE :q OR i.description ILIKE :q OR m.ocr_text ILIKE :q OR m.description ILIKE :q
                OR (m.ai_results::jsonb -> \'symbols\')::text ILIKE :q
             GROUP BY i.id ORDER BY i.id LIMIT :limit',
            ['q' => '%'.addcslashes(trim($query), '%_\\').'%', 'limit' => max(1, min(50, $limit))],
        );

        return array_map(fn ($id) => $this->summary($this->em->find(Item::class, $id)), $ids);
    }

    /**
     * Get one memorial record with its photos, OCR text, proposed people and open questions.
     *
     * @param int $id Record id, as returned by search_memorials
     *
     * @return array<string, mixed>
     */
    #[McpTool(name: 'get_memorial', annotations: new ToolAnnotations(title: 'Get a memorial record', readOnlyHint: true, openWorldHint: false))]
    public function get(int $id): array
    {
        $item = $this->em->find(Item::class, $id) ?? throw new \Mcp\Exception\ToolCallException('No memorial record with id '.$id.'. Use search_memorials to find ids.');
        $record = $item->aiResults['catalog']['record'] ?? [];

        return $this->summary($item) + [
            'description' => $item->description,
            'locationContext' => $item->locationContext,
            'proposedPeople' => $record['people'] ?? [],
            'uncertainties' => $record['uncertainties'] ?? [],
            'sameMarkerAs' => $item->aiResults['sameMarker']['sameAs'] ?? [],
            'tags' => $item->tags,
            'rememberedAs' => isset($item->aiResults[Remembrance::RESULT]['line']) && $item->aiResults[Remembrance::RESULT]['line'] !== ''
                ? ['line' => $item->aiResults[Remembrance::RESULT]['line'], 'basis' => $item->aiResults[Remembrance::RESULT]['basis'], 'note' => 'An AI reading of what is carved; symbols are interpreted, not proven.'] : null,
            'extractedBy' => isset($item->aiResults['catalog']) ? ($item->aiResults['catalog']['provider'] ?? null).' '.($item->aiResults['catalog']['model'] ?? null) : null,
            'note' => 'AI proposals for human review. Not a verified burial record.',
            'photos' => array_values(array_map(fn (Image $image) => $this->photo($image), array_filter($item->media->toArray(), static fn ($m) => $m instanceof Image))),
        ];
    }

    /**
     * List processed records that wait for a person to check them, with the AI's open questions.
     *
     * @param int $limit Maximum number of records (1 to 50)
     *
     * @return list<array<string, mixed>>
     */
    #[McpTool(name: 'list_records_to_review', annotations: new ToolAnnotations(title: 'Records to review', readOnlyHint: true, openWorldHint: false))]
    public function toReview(int $limit = 20): array
    {
        $items = $this->em->getRepository(Item::class)->findBy(['marking' => 'review'], ['id' => 'ASC'], max(1, min(50, $limit)));

        return array_map(fn (Item $item) => $this->summary($item) + ['uncertainties' => $item->aiResults['catalog']['record']['uncertainties'] ?? []], $items);
    }

    /**
     * Count records by processing status and photos by AI treatment, per cemetery.
     *
     * @return array<string, mixed>
     */
    #[McpTool(name: 'collection_overview', annotations: new ToolAnnotations(title: 'Collection overview', readOnlyHint: true, openWorldHint: false))]
    public function overview(): array
    {
        $db = $this->em->getConnection();

        return [
            'cemeteries' => $db->fetchAllAssociative('SELECT c.name, c.locality, COUNT(i.id) AS records FROM cemetery c LEFT JOIN item i ON i.cemetery_id = c.id GROUP BY c.id ORDER BY c.name'),
            'recordsByStatus' => $db->fetchAllKeyValue('SELECT marking, COUNT(*) FROM item GROUP BY marking ORDER BY marking'),
            'photos' => (int) $db->fetchOne('SELECT COUNT(*) FROM media'),
            'photosWithOcr' => (int) $db->fetchOne('SELECT COUNT(*) FROM media WHERE ocr_text IS NOT NULL'),
            'aiCleanedViews' => (int) $db->fetchOne("SELECT COUNT(*) FROM media WHERE image_metadata::jsonb -> 'cleanedView' IS NOT NULL"),
        ];
    }

    /**
     * Every record in one call, each as a dense summary: people, carved words, symbols, scripture and a one-line
     * remembrance. Use it for questions about themes (veterans, hobbies, families, scripture) instead of keyword search.
     *
     * @return array<string, mixed>
     */
    #[McpTool(name: 'collection_digest', annotations: new ToolAnnotations(title: 'Collection digest', readOnlyHint: true, openWorldHint: false))]
    public function digest(): array
    {
        $items = $this->em->getRepository(Item::class)->findBy(['marking' => ['review', 'complete']], ['id' => 'ASC']);

        return [
            'note' => 'AI readings of phone photos, for a person to check. Symbol meanings are interpretations. "Same stone as" links photos of one marker.',
            'records' => array_map(fn (Item $item): array => ['id' => $item->id, 'url' => $this->summary($item)['url'], 'summary' => $item->denseSummary ?? $item->title], $items),
        ];
    }

    /** @return array<string, mixed> */
    private function summary(Item $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'cemetery' => $item->cemetery === null ? null : trim($item->cemetery->name.', '.$item->cemetery->locality, ', '),
            'status' => $item->marking,
            'url' => $this->urls->generate('item_show', ['id' => $item->id], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    /** @return array<string, mixed> */
    private function photo(Image $image): array
    {
        $cleaned = $image->imageMetadata[ImageCleaner::KEY] ?? null;
        $decision = $image->aiResults[Q::RESULT] ?? null;

        return [
            'id' => $image->id,
            'originalName' => $image->originalName,
            'cameraGps' => $image->latitude === null ? null : ['latitude' => $image->latitude, 'longitude' => $image->longitude],
            'originalUrl' => $image->imageUrl === null ? null : $this->images->detail($image),
            'ocrText' => $image->ocrText,
            'jevRoute' => $decision['route'] ?? null,
            'description' => $image->description,
            'symbols' => $image->aiResults[SymbolFinder::RESULT]['items'] ?? null,
            'scripture' => $image->aiResults[SymbolFinder::RESULT]['scripture'] ?? null,
            'aiCleanedView' => $cleaned === null ? null : [
                'url' => $this->images->cleaned($image, ImagePreset::Detail),
                'model' => $cleaned['model'],
                'note' => 'Straightened and relit by an image model for display. It can change letters; the original photo is the evidence.',
            ],
        ];
    }
}
