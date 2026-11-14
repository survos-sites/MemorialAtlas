<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class SymbolList
{
    /**
     * @param list<SymbolNote>     $symbols   Every symbol or image on the main grave marker, one entry each
     * @param list<ScriptureQuote> $scripture Every scripture quote or citation carved on the main grave marker
     */
    public function __construct(public array $symbols, public array $scripture) {}
}
