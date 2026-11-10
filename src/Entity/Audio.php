<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Audio extends Media
{
    public string $kind { get => self::AUDIO; }

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $transcript = null;
}
