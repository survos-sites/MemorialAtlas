<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:restore-legacy-evidence', description: 'Restore existing SQLite upload/OCR/catalog receipts by checksum without overwriting current results.')]
final class RestoreLegacyEvidenceCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em) { parent::__construct(); }
    protected function configure(): void { $this->addArgument('sqlite', InputArgument::REQUIRED); }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = realpath($input->getArgument('sqlite'));
        if ($path === false) throw new \InvalidArgumentException('SQLite receipt database does not exist.');
        $db = new \PDO('sqlite:'.$path);
        $db->exec('PRAGMA query_only = ON');
        $restored = $missing = 0;
        foreach ($db->query('SELECT media.*, item.title AS item_title, item.description AS item_description, item.ai_results AS item_results, item.record_type FROM media JOIN item ON item.id = media.item_id WHERE kind = \'image\'') as $row) {
            $image = $this->em->getRepository(Image::class)->findOneBy(['sha256'=>$row['sha256']]);
            if ($image === null) { ++$missing; continue; }
            if (isset($image->aiResults['legacySqlite'])) continue;
            $legacy = json_decode($row['ai_results'], true, flags: JSON_THROW_ON_ERROR);
            $itemResults = json_decode($row['item_results'], true, flags: JSON_THROW_ON_ERROR);
            $image->sourceUrl ??= $row['source_url'];
            $image->ocrText ??= $row['ocr_text'];
            $image->description ??= $row['description'];
            $metadata = json_decode($row['image_metadata'], true, flags: JSON_THROW_ON_ERROR);
            if (!isset($image->imageMetadata['imagor']) && isset($metadata['imagor'])) $image->imageMetadata['imagor'] = $metadata['imagor'];
            foreach (['ocr','observe'] as $key) {
                if (!isset($image->aiResults[$key]) && isset($legacy[$key])) $image->aiResults[$key] = $legacy[$key];
            }
            // Keep historical model evidence distinct from the new Jev assessment.
            $image->aiResults['legacySqlite'] = ['sha256'=>$row['sha256'], 'media'=>$legacy, 'item'=>$itemResults];
            if (!isset($image->item->aiResults['catalog']) && isset($itemResults['catalog'])) {
                $image->item->aiResults['catalog'] = $itemResults['catalog'];
                $image->item->title = $row['item_title'];
                $image->item->description = $row['item_description'];
                $image->item->recordType = $row['record_type'];
                $image->item->marking = 'review';
                $image->item->processingError = null;
                $image->marking = 'processed';
                $image->processingError = null;
            }
            $this->em->flush();
            $this->em->clear();
            ++$restored;
        }
        (new SymfonyStyle($input,$output))->success("$restored receipts reconciled; $missing photos not yet imported. SQLite was read-only.");
        return $missing ? Command::FAILURE : Command::SUCCESS;
    }
}
