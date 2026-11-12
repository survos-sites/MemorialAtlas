<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\{Cemetery, Item, Media};
use App\Service\{ItemProcessor, MediaStorage};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

#[AsCommand(name: 'app:import', description: 'Import ordered local media and optionally process items synchronously.')]
final class ImportCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, private MediaStorage $storage, private ItemProcessor $processor) { parent::__construct(); }

    protected function configure(): void
    {
        $this->addArgument('directory', InputArgument::REQUIRED)
            ->addOption('manifest', null, InputOption::VALUE_REQUIRED, 'Reviewed capture manifest with ordered groups and SHA-256 checksums')
            ->addOption('images-per-item', null, InputOption::VALUE_REQUIRED, 'Number of consecutive photos per item', '1')
            ->addOption('one-item', null, InputOption::VALUE_REQUIRED, 'Group every selected file into one named item')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Only select the first N files in filename order')
            ->addOption('source', null, InputOption::VALUE_REQUIRED, 'Collection/source label for these photographs')
            ->addOption('donor', null, InputOption::VALUE_REQUIRED, 'Photographer or donor attribution')
            ->addOption('cemetery', null, InputOption::VALUE_REQUIRED, 'Existing cemetery ID, managed in Collection > Cemeteries')
            ->addOption('context', null, InputOption::VALUE_REQUIRED, 'Shared user-supplied location/capture context')
            ->addOption('only', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Select exact filenames, repeat for a sample set')
            ->addOption('text-only', null, InputOption::VALUE_NONE, 'Use OCR and text decisions only; never send images to Jev or vision')
            ->addOption('process', null, InputOption::VALUE_NONE, 'Publish images, run metadata and AI, then save a review draft')
            ->addOption('dry-run', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $directory = $input->getArgument('directory');
        $perItem = filter_var($input->getOption('images-per-item'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $limit = $input->getOption('limit');
        if (!is_dir($directory) || !$perItem || ($limit !== null && !filter_var($limit, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]))) {
            $io->error('Provide a local directory and positive integer limits.');
            return Command::INVALID;
        }
        foreach (['source', 'donor'] as $field) {
            $value = $input->getOption($field);
            if ($value !== null && (trim($value) === '' || mb_strlen($value) > 255)) {
                $io->error('--'.$field.' must be nonempty and at most 255 characters.');
                return Command::INVALID;
            }
        }
        $cemetery = null;
        if ($input->getOption('cemetery') !== null) {
            $cemeteryId = filter_var($input->getOption('cemetery'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $cemetery = $cemeteryId ? $this->em->find(Cemetery::class, $cemeteryId) : null;
            if ($cemetery === null) {
                $io->error('Provide an existing cemetery ID. Create cemeteries in Collection > Cemeteries.');
                return Command::INVALID;
            }
        }
        $files = iterator_to_array((new Finder())->files()->in($directory)->ignoreDotFiles(true)->exclude('__MACOSX')
            ->name('/\.(jpe?g|png|webp|heic|heif|mp3|m4a|wav|ogg|webm)$/i')->sortByName(), false);
        $only = $input->getOption('only');
        if ($only !== []) {
            $files = array_values(array_filter($files, static fn ($file) => in_array($file->getFilename(), $only, true)));
            if (array_diff($only, array_map(static fn ($file) => $file->getFilename(), $files))) {
                $io->error('One or more --only filenames were not found.');
                return Command::INVALID;
            }
        }
        if ($limit !== null) $files = array_slice($files, 0, (int) $limit);
        $name = $input->getOption('one-item');
        if ($name === null) {
            foreach ($files as $file) {
                if (preg_match('/\.(mp3|m4a|wav|ogg|webm)$/i', $file->getFilename())) {
                    $io->error('For mixed photos/audio use --one-item="Title" to keep the capture stream together.');
                    return Command::INVALID;
                }
            }
        }
        $grouping = null;
        $groups = $name !== null ? ($files ? [$files] : []) : array_chunk($files, $perItem);
        if ($input->getOption('manifest') !== null) {
            if ($name !== null || $only !== [] || $limit !== null || $perItem !== 1) {
                $io->error('--manifest cannot be combined with selection or grouping options.');
                return Command::INVALID;
            }
            try {
                $grouping = \App\Service\CaptureManifest::load($input->getOption('manifest'), $directory);
                $byName = [];
                foreach ($files as $file) {
                    if (isset($byName[$file->getFilename()])) throw new \InvalidArgumentException('Duplicate basename in import directory.');
                    $byName[$file->getFilename()] = $file;
                }
                $groups = [];
                foreach ($grouping['groups'] as $group) {
                    $groups[] = array_map(static fn ($file) => $byName[$file] ?? throw new \InvalidArgumentException('Unsupported media: '.$file), $group['files']);
                }
                $files = array_merge(...$groups);
            } catch (\Throwable $error) {
                $io->error($error->getMessage());
                return Command::INVALID;
            }
        }
        $new = $reused = $failed = 0;
        foreach ($groups as $groupIndex => $group) {
            if ($input->getOption('dry-run')) {
                $io->listing(array_map(static fn ($file) => $file->getRelativePathname(), $group));
                continue;
            }
            $existing = [];
            foreach ($group as $file) {
                $hash = hash_file('sha256', $file->getPathname());
                $existing[$file->getPathname()] = $this->em->getRepository(Media::class)->findOneBy(['sha256' => $hash]);
            }
            foreach ($existing as $media) {
                foreach (['source', 'donor'] as $field) {
                    $value = $input->getOption($field);
                    if ($media !== null && $value !== null && $media->$field !== null && $media->$field !== $value) {
                        $io->error('Existing media has a different '.$field.'. Correct attribution explicitly in admin.');
                        return Command::INVALID;
                    }
                }
            }
            $owners = [];
            foreach ($existing as $media) if ($media !== null) $owners[$media->item->id] = $media->item;
            if (count($owners) > 1) {
                $io->error('These photos already belong to separate items. Regroup explicitly before processing.');
                return Command::INVALID;
            }
            $item = $owners ? reset($owners) : new Item($name ?? $group[0]->getBasename('.'.$group[0]->getExtension()));
            if ($grouping !== null && $owners) {
                $selectedHashes = array_map(static fn ($file) => hash_file('sha256', $file->getPathname()), $group);
                foreach ($item->media as $media) {
                    if (!in_array($media->sha256, $selectedHashes, true)) {
                        $io->error('Existing item includes photos outside this manifest group. Regroup explicitly first.');
                        return Command::INVALID;
                    }
                }
            }
            if ($grouping !== null) {
                $item->aiResults['captureGrouping'] = $grouping['groups'][$groupIndex] + [
                    'version' => $grouping['version'], 'method' => $grouping['groupingMethod'],
                    'importBatch' => $grouping['name'], 'gpsUsedForIdentity' => false,
                ];
                $item->locationContext = $grouping['context'];
            }
            if ($cemetery !== null) {
                if ($item->cemetery !== null && $item->cemetery->id !== $cemetery->id) {
                    $io->error('An existing record belongs to a different cemetery. Correct it explicitly in Collection.');
                    return Command::INVALID;
                }
                $item->cemetery = $this->em->getReference(Cemetery::class, $cemetery->id);
            }
            foreach ($group as $file) {
                if ($existing[$file->getPathname()] !== null) {
                    foreach (['source', 'donor'] as $field) {
                        if ($input->getOption($field) !== null) $existing[$file->getPathname()]->$field = $input->getOption($field);
                    }
                    ++$reused; continue;
                }
                if ($item->marking !== 'draft') throw new \RuntimeException('Cannot append media to an item already submitted.');
                $imported = $this->storage->import($item, $file->getPathname());
                foreach (['source', 'donor'] as $field) {
                    if ($input->getOption($field) !== null) $imported->$field = $input->getOption($field);
                }
                ++$new;
            }
            if ($input->getOption('context') !== null) $item->locationContext = $input->getOption('context');
            $this->em->persist($item);
            $this->em->flush();
            if ($input->getOption('process')) {
                $io->section('Item '.$item->id.': '.$item->title);
                try {
                    $this->processor->process($item, static fn (string $step) => $io->writeln($step), $input->getOption('text-only') ? false : null);
                } catch (\Throwable $error) {
                    ++$failed;
                    $io->error($error->getMessage());
                }
            }
            $this->em->clear();
        }
        $io->writeln(sprintf('%d selected files; %d new, %d reused, %d failed items.', count($files), $new, $reused, $failed));
        return $failed ? Command::FAILURE : Command::SUCCESS;
    }
}
