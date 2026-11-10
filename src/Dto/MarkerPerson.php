<?php

declare(strict_types=1);
namespace App\Dto;

final readonly class MarkerPerson
{
    /** @param list<int> $mediaIds */
    public function __construct(
        public string $name,
        public ?string $familyName,
        public ?string $birth,
        public ?string $death,
        public bool $commemorated,
        public array $mediaIds,
        public string $uncertainty,
    ) {}
}
