<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Image;
use App\Service\PhotoMetadata;
use App\Workflow\MediaWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:images:assess', description: 'Run current Jev questions on completed images, retaining their existing catalog drafts.')]
final class AssessImagesCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly PhotoMetadata $metadata, private readonly MediaWorkflow $workflow) { parent::__construct(); }
    protected function configure(): void { $this->addOption('source', null, InputOption::VALUE_REQUIRED, 'Restrict to this source label'); }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $criteria = ['marking'=>'processed'];
        if ($input->getOption('source') !== null) $criteria['source'] = $input->getOption('source');
        $ids = array_map(static fn (Image $i) => $i->id, $this->em->getRepository(Image::class)->findBy($criteria, ['id'=>'ASC']));
        $failed = 0;
        foreach ($ids as $id) {
            $image = $this->em->find(Image::class,$id);
            $io->writeln($image->originalName);
            try {
                $this->metadata->prepare($image);
                $this->workflow->apply($image, MediaWorkflow::ASSESS);
                $this->workflow->apply($image, MediaWorkflow::FINISH);
                $this->em->flush();
            } catch (\Throwable $e) {
                ++$failed;
                $io->error($e->getMessage());
                if (!$this->em->isOpen()) throw $e;
                $image->marking = 'processed';
                $image->processingError = $e->getMessage();
                $this->em->flush();
            }
            $this->em->clear();
        }
        $io->writeln(count($ids)." images assessed; $failed failures. Existing catalog drafts retained.");
        return $failed ? Command::FAILURE : Command::SUCCESS;
    }
}
