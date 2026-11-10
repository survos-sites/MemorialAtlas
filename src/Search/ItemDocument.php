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
        #[Field(facet: true, widget: Widget::Select)] public string $markerOrientation = 'Not assessed',
        #[Field(facet: true, widget: Widget::Select)] public string $markerForm = 'Not assessed',
        #[Field(facet: true, widget: Widget::Select)] public ?int $namedPeopleCount = null,
        #[Field(facet: true, widget: Widget::Select)] public ?int $recordedDeathCount = null,
        #[Field(facet: true, widget: Widget::Select)] public ?int $birthOnlyCount = null,
        #[Field(facet: true, widget: Widget::Select)] public ?int $otherNamedPeopleCount = null,
        #[Field(searchable: true, facet: true, widget: Widget::Select)] public array $symbols = [],
        #[Field(searchable: true, facet: true, widget: Widget::Select)] public array $epitaphThemes = [],
        #[Field(searchable: true)] public ?string $epitaph = null,
        #[Field(searchable: true, facet: true, widget: Widget::Select)] public array $familyName = [],
    ) {}

    public static function fromItem(Item $item): self
    {
        if ($item->id === null) throw new \LogicException('Persist items before indexing.');
        $names = $inscriptions = $observations = $transcripts = $sources = [];
        foreach ($item->aiResults['catalog']['record']['people'] ?? [] as $person) {
            if (is_string($person['name'] ?? null) && trim($person['name']) !== '') $names[] = $person['name'];
            if (is_string($person['inscription'] ?? null)) $inscriptions[] = $person['inscription'];
        }
        $facts = $item->aiResults['markerFacts']['record'] ?? null;
        $people = $facts['people'] ?? [];
        foreach ($people as $person) if (trim($person['name']) !== '') $names[] = $person['name'];
        $hasDate = static fn ($date): bool => is_string($date) && trim($date) !== '';
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
            array_values(array_unique($sources ?: ['Unassigned'])), $item->recordType, $item->marking, $count, $imageId, null,
            $facts['orientation'] ?? 'Not assessed', $facts['form'] ?? 'Not assessed',
            $facts === null ? null : count($people),
            $facts === null ? null : count(array_filter($people, static fn ($p) => $p['commemorated'] && trim($p['uncertainty'] ?? '') === '' && $hasDate($p['death']))),
            $facts === null ? null : count(array_filter($people, static fn ($p) => $p['commemorated'] && trim($p['uncertainty'] ?? '') === '' && $hasDate($p['birth']) && !$hasDate($p['death']))),
            $facts === null ? null : count(array_filter($people, static fn ($p) => !$p['commemorated'])),
            $facts['symbols'] ?? [], $facts['themes'] ?? [], $facts['epitaph'] ?? null,
            array_values(array_unique(array_filter(array_map(static fn ($p) => isset($p['familyName']) && trim($p['familyName']) !== '' ? mb_convert_case(trim($p['familyName']), MB_CASE_TITLE, 'UTF-8') : null, $people)))));
    }
}
