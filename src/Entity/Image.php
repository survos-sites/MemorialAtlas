<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Image extends Media
{
    public string $kind { get => self::IMAGE; }

    #[ORM\Column(nullable: true)]
    public ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    public ?float $longitude = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $imageUrl = null;

    #[ORM\Column(type: 'json', options: ['default' => '[]', 'jsonb' => true])]
    public array $imageMetadata = [];

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $ocrText = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $description = null;
}
