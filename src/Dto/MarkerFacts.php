<?php

declare(strict_types=1);
namespace App\Dto;

final readonly class MarkerFacts
{
    /**
     * @param list<int> $orientationMediaIds
     * @param list<MarkerPerson> $people
     * @param list<string> $symbols
     * @param list<int> $symbolsMediaIds
     * @param list<int> $epitaphMediaIds
     * @param list<string> $themes
     * @param list<string> $uncertainties
     */
    public function __construct(
        public string $orientation,
        public string $form,
        public array $orientationMediaIds,
        public array $people,
        public array $symbols,
        public array $symbolsMediaIds,
        public ?string $epitaph,
        public array $epitaphMediaIds,
        public array $themes,
        public array $uncertainties,
    ) {}
}
