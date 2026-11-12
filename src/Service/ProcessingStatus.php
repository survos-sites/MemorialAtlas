<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\{Image, Item};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

final readonly class ProcessingStatus
{
    public function __construct(private EntityManagerInterface $em, private SetupStatus $setup) {}

    public function pendingCount(): int
    {
        return $this->em->getRepository(Item::class)->count(['marking' => ['draft', 'ready', 'processing', 'failed']]);
    }

    public function missingRequirements(): array
    {
        $needsStorage = (int) $this->em->createQueryBuilder()->select('COUNT(image.id)')->from(Image::class, 'image')
            ->join('image.item', 'item')->where('image.sourceUrl IS NULL')->andWhere('image.marking != :done')
            ->andWhere('item.marking NOT IN (:reviewed)')->setParameter('done', 'processed')->setParameter('reviewed', ['review', 'complete'])
            ->getQuery()->getSingleScalarResult() > 0;
        return array_column(array_filter($this->setup->requirements(), static fn (array $entry) =>
            $entry['required'] && !$entry['configured'] && ($needsStorage || $entry['name'] !== 'S3 endpoint, bucket and credentials')
        ), 'name');
    }

    #[AsCommand('app:processing:status', 'Report photographs awaiting processing and missing configuration without making AI calls')]
    public function report(SymfonyStyle $io): int
    {
        $rows = $this->em->createQueryBuilder()
            ->select('image.marking AS status', 'COUNT(image.id) AS photos')
            ->from(Image::class, 'image')->groupBy('image.marking')->getQuery()->getArrayResult();
        $io->table(['Photo status', 'Count'], array_map(static fn (array $row) => [$row['status'], $row['photos']], $rows));
        $pending = array_sum(array_map(static fn (array $row) => $row['status'] === 'processed' ? 0 : (int) $row['photos'], $rows));
        if ($pending) {
            $io->warning(sprintf('%d photographs still need processing or retry. Saved results remain available; no processing was started.', $pending));
        }
        $missing = $this->missingRequirements();
        if ($missing) {
            $io->note('Browsing saved results needs no AI keys. Before processing additional photos, configure these in .env.local:');
            $io->listing($missing);
        }
        $io->note('The stock Imagor container creates thumbnails. Processing requires the separate Imagor /meta OCR tooling described in README.md. This checks configuration presence, not service connectivity.');
        return 0;
    }
}
