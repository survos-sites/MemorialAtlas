<?php

declare(strict_types=1);

namespace App\Search;

use App\Entity\{Audio, Image, Item};
use Survos\FieldBundle\Attribute\Field;
use Survos\FieldBundle\Enum\Widget;

final readonly class ItemDocument
{
    public function __construct(
        #[Field] public int $id,
        #[Field(searchable: true, sortable: true)] public string $title,
        #[Field(searchable: true)] public ?string $description,
        #[Field(searchable: true)] public array $names,
        #[Field(searchable: true)] public string $inscriptions,
        #[Field(searchable: true)] public string $observations,
        #[Field(searchable: true)] public string $transcripts,
        #[Field(searchable: true, facet: true, widget: Widget::Select)] public string $cemetery,
        #[Field(facet: true, widget: Widget::Select)] public array $source,
        #[Field(facet: true, widget: Widget::Select)] public string $recordType,
        #[Field(facet: true, widget: Widget::Select)] public string $reviewStatus,
        #[Field(facet: true, widget: Widget::Select)] public int $imageCount,
        #[Field] public ?int $imageId,
        #[Field] public ?string $thumbnailUrl = null,
    ) {}

    public static function fromItem(Item $item): self
    {
        if ($item->id === null) throw new \LogicException('Persist items before indexing.');
        $names = $inscriptions = $observations = $transcripts = $sources = [];
        foreach ($item->aiResults['catalog']['record']['people'] ?? [] as $person) {
            if (is_string($person['name'] ?? null) && trim($person['name']) !== '') $names[] = $person['name'];
            if (is_string($person['inscription'] ?? null)) $inscriptions[] = $person['inscription'];
        }
        $count = 0; $imageId = null;
        foreach ($item->media as $media) {
            $sources[] = $media->source ?? 'Unassigned';
            if ($media instanceof Image) {
                ++$count; $imageId ??= $media->id;
                if ($media->ocrText) $inscriptions[] = $media->ocrText;
                if ($media->description) $observations[] = $media->description;
            } elseif ($media instanceof Audio && $media->transcript) {
                $transcripts[] = $media->transcript;
            }
        }
        return new self($item->id, $item->title, $item->description, array_values(array_unique($names)),
            implode("\n", array_unique($inscriptions)), implode("\n", $observations), implode("\n", $transcripts),
            $item->cemetery === null ? 'Unassigned' : (string) $item->cemetery,
            array_values(array_unique($sources ?: ['Unassigned'])), $item->recordType, $item->marking, $count, $imageId);
    }
}
