<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Item;
use App\Service\GedcomExporter;
use Doctrine\ORM\EntityManagerInterface;
use Korin\Gedcom\{Gedcom, Version};
use Symfony\Component\Console\Attribute\{AsCommand, Option};
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(name: 'app:gedcom:export', description: 'Write the people named on the stones as a GEDCOM file.')]
final class GedcomExportCommand
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly GedcomExporter $exporter,
        private readonly UrlGeneratorInterface $urls,
    ) {}

    public function __invoke(
        SymfonyStyle $io,
        #[Option(description: 'File to write')] string $output = 'var/mematlas.ged',
        #[Option(description: 'GEDCOM version: 5.5.1, 5.5.5 or 7.0', name: 'gedcom-version')] string $gedcomVersion = '5.5.1',
    ): int {
        $version = Version::tryFrom($gedcomVersion);
        if (null === $version) {
            $io->error(sprintf('Unknown GEDCOM version "%s".', $gedcomVersion));

            return 2;
        }

        $items = $this->em->getRepository(Item::class)->findBy(['recordType' => 'gravestone'], ['id' => 'ASC']);
        $ged = $this->exporter->export(
            $items,
            fn (Item $item): string => $this->urls->generate('item_show', ['id' => $item->id], UrlGeneratorInterface::ABSOLUTE_URL),
            $version,
        );
        file_put_contents($output, $ged);

        $doc = Gedcom::read($output);
        $report = Gedcom::validate($doc);
        $io->success(sprintf('%d people from %d stones written to %s (GEDCOM %s).', count($doc->individuals()), count($doc->sources()), $output, $version->value));
        foreach ($report->diagnostics ?? [] as $diagnostic) {
            $io->warning((string) $diagnostic);
        }

        return $report->isValid() ? 0 : 1;
    }
}
