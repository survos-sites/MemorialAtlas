<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\{Image, Item};
use Doctrine\ORM\EntityManagerInterface;
use Twig\Attribute\AsTwigFunction;

final readonly class ChatExtension
{
    public const MAX_STONES = 8;

    public function __construct(private EntityManagerInterface $em) {}

    /**
     * The stones an answer links to, in the order of their first link, each with its first photo.
     *
     * @return list<array{item: Item, photo: Image}>
     */
    #[AsTwigFunction('chat_stones')]
    public function stones(string $answer): array
    {
        preg_match_all('~/items/(\d+)~', $answer, $matches);
        $ids = array_slice(array_values(array_unique(array_map(intval(...), $matches[1]))), 0, self::MAX_STONES);
        if ($ids === []) return [];

        $items = [];
        foreach ($this->em->getRepository(Item::class)->findBy(['id' => $ids]) as $item) $items[$item->id] = $item;
        $stones = [];
        foreach ($ids as $id) {
            $photo = ($items[$id] ?? null)?->media->findFirst(static fn ($key, $media): bool => $media instanceof Image);
            if ($photo !== null) $stones[] = ['item' => $items[$id], 'photo' => $photo];
        }

        return $stones;
    }
}
