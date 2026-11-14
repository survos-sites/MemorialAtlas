<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class SymbolNote
{
    /**
     * @param string $name    What is carved, etched or attached, in a few plain words, for example "crossed hockey sticks"
     * @param string $meaning What it usually says about the person, for example "played or loved hockey"; empty when unknown
     */
    public function __construct(
        public string $name,
        public SymbolCategory $category,
        public string $meaning,
    ) {}
}
