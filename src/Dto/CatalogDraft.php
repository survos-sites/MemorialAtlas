<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class CatalogDraft
{
    /**
     * @param list<CatalogPerson> $people
     * @param list<string> $uncertainties
     */
    public function __construct(
        public string $title,
        public string $description,
        public RecordType $recordType,
        public array $people,
        public array $uncertainties,
    ) {}
}
