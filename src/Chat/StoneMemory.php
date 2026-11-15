<?php

declare(strict_types=1);

namespace App\Chat;

use App\Service\MemorialVectors;
use Symfony\AI\Agent\Input;
use Symfony\AI\Agent\Memory\{Memory, MemoryProviderInterface};
use Symfony\AI\Platform\Message\Content\Text;
use Symfony\AI\Platform\Message\UserMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Adds the stones nearest in meaning to the question to the system prompt, so the first model call can
 * answer without a tool round. The stock EmbeddingProvider returns 5 documents as raw metadata JSON;
 * this one returns 20 with their links. The memory is not saved with the conversation.
 */
final readonly class StoneMemory implements MemoryProviderInterface
{
    public const LIMIT = 20;

    public function __construct(private MemorialVectors $vectors, private UrlGeneratorInterface $urls) {}

    public function load(Input $input): array
    {
        $last = array_last($input->getMessageBag()->getMessages());
        if (!$last instanceof UserMessage) return [];
        $question = implode(' ', array_map(static fn (Text $t): string => $t->getText(), array_filter($last->getContent(), static fn ($c): bool => $c instanceof Text)));
        if (trim($question) === '') return [];

        $lines = array_map(fn (array $hit): string => sprintf('- [%s](%s) (record %d): %s', $hit['title'], $this->urls->generate('item_show', ['id' => $hit['id']], UrlGeneratorInterface::ABSOLUTE_URL), $hit['id'], $hit['summary']),
            $this->vectors->find($question, self::LIMIT));

        return $lines === [] ? [] : [new Memory("## Stones nearest in meaning to the question\n\nNearest first. Not every stone matches: keep only those whose summary supports the answer.\n\n".implode("\n", $lines))];
    }
}
