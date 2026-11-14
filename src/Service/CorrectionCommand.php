<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Image;
use App\Message\ProcessItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\{AsCommand, Argument, Option};
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

final readonly class CorrectionCommand
{
    public function __construct(private EntityManagerInterface $em, private MessageBusInterface $bus, private PerspectiveCorrector $corrector) {}

    #[AsCommand('app:image:correct', 'Correct one photograph and OCR it using the existing item message handler')]
    public function run(SymfonyStyle $io, #[Argument('Image ID')] int $id, #[Option('Four normalized [x,y] corners, clockwise from top-left; omit to reuse the saved selection')] ?string $corners = null, #[Option('Run the same message handler immediately')] bool $sync = false, #[Option('Skip correction and the OpenCV requirement')] bool $noCorrection = false): int
    {
        if (!PerspectiveCorrector::enabled(!$noCorrection)) { $io->note('Correction disabled.'); return 0; }
        $image = $this->em->find(Image::class, $id) ?? throw new \InvalidArgumentException('Image not found.');
        $points = $corners === null ? ($image->imageMetadata['correctionCorners'] ?? throw new \InvalidArgumentException('Select four corners first, or supply --corners.')) : json_decode($corners, true, flags: JSON_THROW_ON_ERROR);
        $this->corrector->points($points, 1600, 1600);
        $image->imageMetadata['correctionCorners'] = $points;
        $image->imageMetadata['correctionStatus'] = 'queued';
        $this->em->flush();
        $this->bus->dispatch(new ProcessItem($image->item->id, true, $id, $points), $sync ? [new TransportNamesStamp('sync')] : []);
        $io->success($sync ? 'Correction and OCR completed.' : 'Correction queued on the existing processing transport.');
        return 0;
    }
}
