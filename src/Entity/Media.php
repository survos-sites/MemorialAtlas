<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'kind', type: 'string', length: 16)]
#[ORM\DiscriminatorMap(['image' => Image::class, 'audio' => Audio::class])]
#[ORM\UniqueConstraint(name: 'media_item_position', columns: ['item_id', 'position'])]
abstract class Media
{
    public const IMAGE = 'image';
    public const AUDIO = 'audio';

    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    #[Groups(['gravestone:read'])]
    public private(set) ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'media')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public private(set) Item $item;

    #[ORM\Column]
    #[Groups(['gravestone:read'])]
    public private(set) int $position;

    abstract public string $kind { get; }

    #[ORM\Column(length: 32)]
    public string $marking = 'uploaded';

    #[ORM\Column(length: 255)]
    public private(set) string $originalName;

    #[ORM\Column(length: 255)]
    public private(set) string $storagePath;

    #[ORM\Column(length: 100)]
    #[Groups(['gravestone:read'])]
    public private(set) string $mimeType;

    #[ORM\Column(length: 64)]
    #[Groups(['gravestone:read'])]
    public private(set) string $sha256;

    #[ORM\Column(length: 255, nullable: true)]
    #[\Symfony\Component\Validator\Constraints\Length(max: 255)]
    public ?string $source = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[\Symfony\Component\Validator\Constraints\Length(max: 255)]
    public ?string $donor = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $sourceUrl = null;

    #[ORM\Column(type: 'json', options: ['jsonb' => true])]
    public array $aiResults = [];

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $processingError = null;

    #[ORM\Column]
    #[Groups(['gravestone:read'])]
    public \DateTimeImmutable $createdAt;

    public function __construct(Item $item, int $position, string $originalName, string $storagePath, string $mimeType, string $sha256)
    {
        if ($position < 0) {
            throw new \InvalidArgumentException('Invalid capture position.');
        }
        $this->item = $item;
        $this->position = $position;
        $this->originalName = $originalName;
        $this->storagePath = $storagePath;
        $this->mimeType = $mimeType;
        $this->sha256 = $sha256;
        $this->createdAt = new \DateTimeImmutable();
        $item->addMedia($this);
    }

    #[Groups(['gravestone:read'])]
    public function getContentUrl(): string
    {
        return '/media/'.$this->id;
    }

    public function getMarking(): string { return $this->marking; }
    public function setMarking(string $marking, array $context = []): void { $this->marking = $marking; }
    public function __toString(): string { return $this->originalName; }
}
