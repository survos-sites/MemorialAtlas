<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Audio extends Media
{
    #[Groups(['gravestone:read'])]
    public string $kind { get => self::AUDIO; }

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['gravestone:read'])]
    public ?string $transcript = null;
}
