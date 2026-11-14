<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Image;
use App\Service\ImageCleaner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\{Argument, AsCommand, Option};
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:clean-images', 'Add AI-cleaned display copies to processed gravestone photos')]
final class CleanImagesCommand
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly ImageCleaner $cleaner) {}

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Item ids; all items when empty')] array $items = [],
        #[Option('Stop after this many photos')] ?int $limit = null,
    ): int {
        $qb = $this->em->getRepository(Image::class)->createQueryBuilder('m')->join('m.item', 'i')->orderBy('i.id')->addOrderBy('m.position');
        if ($items !== []) $qb->andWhere('i.id IN (:ids)')->setParameter('ids', array_map('intval', $items));
        $made = $done = 0;
        foreach ($qb->getQuery()->toIterable() as $image) {
            if ($limit !== null && $done >= $limit) break;
            if (!ImageCleaner::eligible($image)) continue;
            ++$done;
            $had = $image->imageMetadata[ImageCleaner::KEY]['inputHash'] ?? null;
            try {
                $this->cleaner->clean($image, static fn (string $m) => $io->writeln(sprintf('  item %d, photo %d: %s', $image->item->id, $image->id, $m)));
                unset($image->aiResults['cleanedViewError']);
            } catch (\Throwable $e) {
                $image->aiResults['cleanedViewError'] = mb_substr($e->getMessage(), 0, 500);
                $io->warning(sprintf('Photo %d: %s', $image->id, $e->getMessage()));
            }
            if (($image->imageMetadata[ImageCleaner::KEY]['inputHash'] ?? null) !== $had) ++$made;
            $this->em->flush();
        }
        $io->success(sprintf('%d eligible photos checked, %d new cleaned views.', $done, $made));

        return 0;
    }
}
