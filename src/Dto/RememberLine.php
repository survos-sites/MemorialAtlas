<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class RememberLine
{
    /**
     * @param string       $line  One sentence, or empty when the stone shows only names and dates
     * @param list<string> $basis The carved words or symbols each claim rests on
     */
    public function __construct(public string $line, public array $basis) {}
}
