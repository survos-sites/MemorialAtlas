<?php

declare(strict_types=1);
namespace App\Dto;

final readonly class AerialLayout
{
    /**
     * @param list<AerialMarker> $markers
     * @param list<string> $uncertainties
     */
    public function __construct(
        public array $markers,
        public int $rows,
        public string $rowDirection,
        public array $uncertainties,
    ) {}
}
