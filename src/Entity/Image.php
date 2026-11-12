<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Image extends Media
{
    public function getThumbnailSource(): string
    {
        $source = $this->sourceUrl ?: $this->storagePath;
        return str_starts_with($source, 's3://') ? substr($source, 5) : $source;
    }

    #[Groups(['gravestone:read'])]
    public string $kind { get => self::IMAGE; }

    #[ORM\Column(nullable: true)]
    #[Groups(['gravestone:read'])]
    public ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['gravestone:read'])]
    public ?float $longitude = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $imageUrl = null;

    #[ORM\Column(type: 'json', options: ['default' => '[]', 'jsonb' => true])]
    public array $imageMetadata = [];

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['gravestone:read'])]
    public ?string $ocrText = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['gravestone:read'])]
    public ?string $description = null;
}
