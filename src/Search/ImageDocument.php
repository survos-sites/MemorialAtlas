<?php

declare(strict_types=1);

namespace App\Search;

use App\Decision\ImageQuestions as Q;
use App\Entity\Image;
use Survos\FieldBundle\Attribute\Field;
use Survos\FieldBundle\Enum\Widget;

final readonly class ImageDocument
{
    public function __construct(
        #[Field] public int $id,
        #[Field] public int $itemId,
        #[Field(searchable: true, sortable: true)] public string $title,
        #[Field(searchable: true)] public array $names,
        #[Field(searchable: true)] public ?string $inscription,
        #[Field(searchable: true)] public ?string $description,
        #[Field(facet: true, widget: Widget::Select)] public string $source,
        #[Field(facet: true, widget: Widget::Select)] public string $donor,
        #[Field(searchable: true, facet: true, widget: Widget::Select)] public string $cemetery,
        #[Field(facet: true, widget: Widget::Select)] public string $imageType,
        #[Field(facet: true, widget: Widget::Select)] public string $photoRole,
        #[Field(facet: true, widget: Widget::Select)] public string $memorial,
        #[Field(facet: true, widget: Widget::Select)] public string $flowers,
        #[Field(facet: true, widget: Widget::Select)] public string $veteranMarker,
        #[Field(facet: true, widget: Widget::Select)] public string $ocrUsability,
        #[Field(facet: true, widget: Widget::Select)] public string $reviewStatus,
        #[Field] public ?string $thumbnailUrl = null,
        #[Field] public bool $aiCleaned = false,
    ) {}

    public static function fromImage(Image $image): self
    {
        if ($image->id === null || $image->item->id === null) throw new \LogicException('Persist images before indexing.');
        $answers = $image->aiResults[Q::RESULT]['answers'] ?? [];
        $answer = static fn (string $key): string => isset($answers[$key]) ? self::label($key, $answers[$key]['choice']) : 'Not assessed';
        $quality = $image->aiResults[Q::TEXT_RUN]['answers'][Q::QUALITY] ?? null;
        $people = $image->item->aiResults['catalog']['record']['people'] ?? [];
        $names = [];
        foreach ($people as $person) {
            if (is_string($person['name'] ?? null) && trim($person['name']) !== '') $names[] = $person['name'];
        }
        return new self(
            $image->id, $image->item->id, $image->item->title, array_values(array_unique($names)),
            $image->ocrText, $image->description ?? $image->item->description,
            $image->source ?? 'Unassigned', $image->donor ?? 'Unassigned',
            $image->item->cemetery === null ? 'Unassigned' : (string) $image->item->cemetery,
            $answer(Q::TYPE), $answer(Q::ROLE), $answer(Q::MEMORIAL), $answer(Q::FLOWERS), $answer(Q::MARKER),
            $quality === null ? 'Not assessed' : self::label(Q::QUALITY, $quality['choice']),
            $image->item->marking,
        );
    }

    private static function label(string $key, string $value): string
    {
        return match ($key.':'.$value) {
            Q::MEMORIAL.':one' => 'Single memorial',
            Q::MEMORIAL.':multiple' => 'Shared memorial',
            Q::TYPE.':place_reference' => 'Place reference',
            Q::MARKER.':associated' => 'Associated marker',
            default => ucfirst(str_replace('_', ' ', $value)),
        };
    }
}
