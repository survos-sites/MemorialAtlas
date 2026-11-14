<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Image;
use App\Service\MediaStorage;
use App\Service\PhotoMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/** Saves the camera's own EXIF (including Orientation) for photographs imported before it was kept. */
#[AsCommand(name: 'app:media:exif', description: 'Store the original EXIF of photographs that lack it (needs the file in var/media).')]
final class BackfillExifCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly MediaStorage $storage)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Stop after this many photographs', '0');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = (int) $input->getOption('limit');
        $saved = $missing = 0;
        foreach ($this->em->getRepository(Image::class)->findAll() as $image) {
            if (isset($image->imageMetadata['exif']) && $image->imageMetadata['exif'] !== []) continue;
            $exif = PhotoMetadata::readExif($this->storage->path($image));
            if ($exif === []) { ++$missing; continue; }
            $image->imageMetadata['exif'] = $exif;
            ++$saved;
            if ($limit > 0 && $saved >= $limit) break;
        }
        $this->em->flush();
        $io->success(sprintf('EXIF stored for %d photographs; %d had no local file or no EXIF.', $saved, $missing));

        return Command::SUCCESS;
    }
}
