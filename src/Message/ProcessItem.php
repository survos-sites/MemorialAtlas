<?php
declare(strict_types=1);
namespace App\Message;
final readonly class ProcessItem
{
    public function __construct(
        public int $itemId,
        public ?bool $correction = null,
        public ?int $correctionImageId = null,
        public ?array $corners = null,
    ) {}
}
