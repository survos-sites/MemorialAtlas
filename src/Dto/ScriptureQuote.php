<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class ScriptureQuote
{
    /**
     * @param string $text     The quoted words as carved, without the citation; empty when only a citation is carved
     * @param string $citation The citation as carved, for example "Matthew 19:14"; empty when none is carved
     * @param string $book     The book of the Bible (or other scripture) the quote comes from, for example "Matthew"; empty when unknown
     */
    public function __construct(
        public string $text,
        public string $citation,
        public string $book,
    ) {}
}
