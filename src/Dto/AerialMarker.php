<?php

declare(strict_types=1);
namespace App\Dto;

final readonly class AerialMarker
{
    /**
     * @param list<int> $box2d [ymin, xmin, ymax, xmax], each normalized to 0-1000
     */
    public function __construct(
        public array $box2d,
        public string $form,
        public string $color,
    ) {}
}
