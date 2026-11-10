<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class CatalogPerson
{
    public function __construct(
        public ?string $name,
        public ?string $birth,
        public ?string $death,
        public ?string $inscription,
    ) {}
}
