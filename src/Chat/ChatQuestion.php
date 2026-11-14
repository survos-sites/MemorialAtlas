<?php

declare(strict_types=1);

namespace App\Chat;

use Symfony\Component\Validator\Constraints as Assert;

final class ChatQuestion
{
    public function __construct(
        #[Assert\NotBlank, Assert\Length(max: 500)]
        public readonly string $question = '',
    ) {}
}
