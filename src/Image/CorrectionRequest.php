<?php

declare(strict_types=1);

namespace App\Image;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\Validator\Constraints as Assert;

#[Exclude]
final readonly class CorrectionRequest
{
    public function __construct(
        #[Assert\Count(exactly: 4)] public array $corners,
        #[Assert\NotBlank] public string $token,
    ) {}
}
