<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class CheckedSummary
{
    /**
     * @param string                    $summary       One dense paragraph about the people on the marker, with only what the photo confirms
     * @param list<ReadingDisagreement> $disagreements Every stored reading that the photo does not support
     */
    public function __construct(public string $summary, public array $disagreements) {}
}
