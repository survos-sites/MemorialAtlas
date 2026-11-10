<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Item
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    public private(set) ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank, Assert\Length(max: 255)]
    public string $title = 'Untitled item';

    #[ORM\ManyToOne(targetEntity: Cemetery::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'RESTRICT')]
    public ?Cemetery $cemetery = null;

    #[ORM\Column(length: 32)]
    public string $marking = 'draft';

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $locationContext = null;

    #[ORM\Column(length: 32, options: ['default' => 'object'])]
    public string $recordType = 'object';

    #[ORM\Column(type: 'json', options: ['jsonb' => true])]
    public array $aiResults = [];

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $processingError = null;

    #[ORM\Column]
    public \DateTimeImmutable $createdAt;

    /** @var Collection<int, Media> */
    #[ORM\OneToMany(mappedBy: 'item', targetEntity: Media::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    public private(set) Collection $media;

    public function __construct(string $title = 'Untitled item')
    {
        $this->title = $title;
        $this->createdAt = new \DateTimeImmutable();
        $this->media = new ArrayCollection();
    }

    public function addMedia(Media $media): void
    {
        if ($media->item !== $this) {
            throw new \InvalidArgumentException('Media belongs to another item.');
        }
        if (!$this->media->contains($media)) {
            $this->media->add($media);
        }
    }

    public function getMarking(): string { return $this->marking; }
    public function setMarking(string $marking, array $context = []): void { $this->marking = $marking; }
    public function __toString(): string { return $this->title; }
}
