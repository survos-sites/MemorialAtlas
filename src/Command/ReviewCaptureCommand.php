<?php

declare(strict_types=1);

namespace App\Command;

use App\Decision\{CaptureQuestions, JevClient};
use App\Entity\{Cemetery, Image};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:capture:review', description: 'Record Jev suggestions for consecutive photos without merging items.')]
final class ReviewCaptureCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly JevClient $jev) { parent::__construct(); }

    protected function configure(): void
    {
        $this->addArgument('cemetery', InputArgument::REQUIRED, 'Existing cemetery ID')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum comparisons this run')
            ->addOption('dry-run', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = filter_var($input->getArgument('cemetery'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $limit = $input->getOption('limit');
        if (!$id || !$this->em->find(Cemetery::class, $id) || ($limit !== null && !filter_var($limit, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]))) {
            $output->writeln('Provide an existing cemetery and a positive limit.');
            return Command::INVALID;
        }
        $images = $this->em->createQuery('SELECT m, i FROM App\Entity\Image m JOIN m.item i WHERE i.cemetery = :cemetery ORDER BY m.originalName, m.id')
            ->setParameter('cemetery', $id)->getResult();
        $count = $failed = 0;
        for ($n = 1; $n < count($images); ++$n) {
            if ($limit !== null && $count >= (int) $limit) break;
            $previous = $images[$n - 1]; $current = $images[$n];
            if ($previous->marking !== 'processed' || $current->marking !== 'processed') continue;
            $state = ['previous' => self::evidence($previous), 'current' => self::evidence($current)];
            $hash = hash('sha256', json_encode([$state, CaptureQuestions::VERSION, $this->jev->model], JSON_THROW_ON_ERROR));
            if (($current->aiResults['captureReview']['inputHash'] ?? null) === $hash) continue;
            if ($input->getOption('dry-run')) { $output->writeln($previous->originalName.' → '.$current->originalName); ++$count; continue; }
            try {
                $run = $this->jev->evaluate($state, CaptureQuestions::questions());
                $current->aiResults['captureReview'] = $run + [
                    'protocol' => CaptureQuestions::VERSION, 'inputHash' => $hash,
                    'previousMediaId' => $previous->id, 'previousItemId' => $previous->item->id,
                    'evidenceMode' => 'ocr_and_descriptions', 'action' => 'review_only',
                ];
                $this->em->flush();
                $output->writeln($current->originalName.': '.$run['answers']['relationship']['choice']);
            } catch (\Throwable $e) {
                ++$failed; $output->writeln('Failed '.$current->originalName.': '.$e->getMessage());
            }
            ++$count;
        }
        $output->writeln(sprintf('%d comparisons, %d failures. No items merged.', $count, $failed));
        return $failed ? Command::FAILURE : Command::SUCCESS;
    }

    private static function evidence(Image $image): array
    {
        return ['mediaId' => $image->id, 'sha256' => $image->sha256, 'filename' => $image->originalName,
            'ocr' => $image->ocrText, 'observation' => $image->description,
            'captureTime' => $image->imageMetadata['capture']['dateTimeOriginal'] ?? null,
            'cameraPosition' => ['latitude' => $image->latitude, 'longitude' => $image->longitude],
        ];
    }
}
