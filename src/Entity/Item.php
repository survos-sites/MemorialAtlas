<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\{ApiResource, Get, GetCollection, QueryParameter};
use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Gravestone',
    operations: [
        new Get(uriTemplate: '/gravestones/{id}'),
        new GetCollection(uriTemplate: '/gravestones', parameters: [
            'cemetery' => new QueryParameter(property: 'cemetery.id', filter: new ExactFilter()),
            'recordType' => new QueryParameter(property: 'recordType', filter: new ExactFilter()),
        ]),
    ],
    normalizationContext: ['groups' => ['gravestone:read']],
    paginationItemsPerPage: 30,
    order: ['id' => 'ASC'],
)]
#[ORM\Entity]
class Item
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    #[Groups(['gravestone:read'])]
    public private(set) ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank, Assert\Length(max: 255)]
    #[Groups(['gravestone:read'])]
    public string $title = 'Untitled item';

    #[ORM\ManyToOne(targetEntity: Cemetery::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'RESTRICT')]
    #[Groups(['gravestone:read'])]
    public ?Cemetery $cemetery = null;

    #[ORM\Column(length: 32)]
    #[Groups(['gravestone:read'])]
    public string $marking = 'draft';

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['gravestone:read'])]
    public ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['gravestone:read'])]
    public ?string $locationContext = null;

    #[ORM\Column(length: 32, options: ['default' => 'object'])]
    #[Groups(['gravestone:read'])]
    public string $recordType = 'object';

    #[ORM\Column(type: 'json', options: ['jsonb' => true])]
    public array $aiResults = [];

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $processingError = null;

    #[ORM\Column]
    #[Groups(['gravestone:read'])]
    public \DateTimeImmutable $createdAt;

    /** @var Collection<int, Media> */
    #[ORM\OneToMany(mappedBy: 'item', targetEntity: Media::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[Groups(['gravestone:read'])]
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

    /** Extracted claims retain their model and review status, without private request payloads. */
    #[Groups(['gravestone:read'])]
    public function getClaims(): array
    {
        $claims = [];
        foreach (['catalog', 'markerFacts', 'captureGrouping'] as $stage) {
            if (!isset($this->aiResults[$stage])) {
                continue;
            }
            $result = $this->aiResults[$stage];
            $claims[$stage] = array_intersect_key($result, array_flip(['record', 'provider', 'model', 'status', 'basis']));
        }

        return $claims;
    }

    public function getMarking(): string { return $this->marking; }
    #[Groups(['gravestone:read'])]
    public function setMarking(string $marking, array $context = []): void { $this->marking = $marking; }
    public function __toString(): string { return $this->title; }
}
