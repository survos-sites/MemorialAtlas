<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Cemetery
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    #[Groups(['gravestone:read'])]
    public private(set) ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank, Assert\Length(max: 255)]
    #[Groups(['gravestone:read'])]
    public string $name = '';

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Groups(['gravestone:read'])]
    public ?string $locality = null;

    public function __toString(): string
    {
        return $this->name.($this->locality !== null ? ' ('.$this->locality.')' : '');
    }
}
