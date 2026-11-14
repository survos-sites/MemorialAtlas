<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class ReadingDisagreement
{
    /**
     * @param string $field      What was read, for example "inscription", "death date", "symbol"
     * @param string $reading    What the stored reading says
     * @param string $photoShows What the photo shows instead, or "not visible"
     */
    public function __construct(public string $field, public string $reading, public string $photoShows) {}
}
