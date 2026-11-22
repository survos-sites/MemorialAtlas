<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\{Image, Item};
use App\Service\ImageDelivery;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\{Argument, AsCommand};
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Photos of flat markers taken straight down often carry the wrong EXIF orientation. This stores a display
 * rotation on the photo that each item's search card shows; ImageDelivery applies it. Run elastic:spool:flush
 * afterwards, because search documents store the thumbnail URL.
 */
#[AsCommand('app:image:rotate', 'Turn the card photo of each item for display (Imagor rotate: 90, 180 or 270 counter-clockwise; 0 clears)')]
final readonly class RotateImageCommand
{
    public function __construct(private EntityManagerInterface $em) {}

    /** @param list<string> $items */
    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Counter-clockwise degrees: 0, 90, 180 or 270')] int $degrees,
        #[Argument('Item IDs')] array $items,
    ): int {
        if (!in_array($degrees, [0, 90, 180, 270], true)) {
            $io->error('Degrees must be 0, 90, 180 or 270.');

            return 1;
        }

        $rows = [];
        foreach ($items as $id) {
            $item = $this->em->find(Item::class, (int) $id);
            $image = $item?->media->findFirst(static fn ($key, $media): bool => $media instanceof Image);
            if (!$image instanceof Image) {
                $io->warning(sprintf('Item %s has no photo.', $id));
                continue;
            }
            if ($degrees === 0) {
                unset($image->imageMetadata[ImageDelivery::ROTATION]);
            } else {
                $image->imageMetadata[ImageDelivery::ROTATION] = $degrees;
            }
            $rows[] = [$item->id, $image->id, $degrees];
        }
        $this->em->flush();

        $io->table(['Item', 'Image', 'Degrees'], $rows);
        $io->success(sprintf('%d photos updated. Run elastic:spool:flush to refresh search thumbnails.', count($rows)));

        return 0;
    }
}
