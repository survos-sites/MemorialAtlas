<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Image;
use App\Service\MediaStorage;
use App\Service\RustoOcr;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Runs stored photographs through every model of the local rusto OCR service and, where an image
 * already has a transcript from a stronger OCR (default: Mistral), scores each model against it.
 * The reference is another machine's reading, not ground truth: it measures agreement.
 */
#[AsCommand(name: 'app:ocr:compare-models', description: 'Compare the local rusto OCR models on stored photographs.')]
final class CompareOcrModelsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MediaStorage $storage,
        private readonly RustoOcr $ocr,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Stop after this many images', '0')
            ->addOption('models', null, InputOption::VALUE_REQUIRED, 'Comma-separated models (default: all the service offers)')
            ->addOption('reference', null, InputOption::VALUE_REQUIRED, 'Provider whose stored OCR is the reference', 'mistral-ocr-latest')
            ->addOption('reference-only', null, InputOption::VALUE_NONE, 'Only images that have a reference transcript')
            ->addOption('out', null, InputOption::VALUE_REQUIRED, 'Write one JSON line per image here');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $models = $input->getOption('models') ? explode(',', (string) $input->getOption('models')) : $this->ocr->models();
        $limit = (int) $input->getOption('limit');
        $provider = (string) $input->getOption('reference');
        $out = $input->getOption('out') ? fopen((string) $input->getOption('out'), 'w') : null;

        $ids = $this->em->getConnection()->fetchFirstColumn(
            "select id from media where kind = 'image' and mime_type in ('image/jpeg','image/png','image/webp') order by id"
        );
        $stats = array_fill_keys($models, ['n' => 0, 'errors' => 0, 'ms' => 0, 'chars' => 0, 'lines' => 0, 'refN' => 0, 'recall' => 0.0, 'sim' => 0.0]);
        $wins = ['tie' => 0] + array_fill_keys($models, 0);
        $done = 0;
        $warm = [];

        foreach ($ids as $id) {
            $image = $this->em->find(Image::class, $id);
            $reference = ($image->aiResults['ocr']['provider'] ?? $image->aiResults['ocr']['model'] ?? null) === $provider ? (string) $image->ocrText : null;
            if ($input->getOption('reference-only') && (null === $reference || '' === trim($reference))) {
                continue;
            }
            $path = $this->storage->path($image);
            if (!is_file($path)) {
                continue;
            }
            $bytes = (string) file_get_contents($path);
            $row = ['id' => $id, 'name' => $image->originalName, 'reference' => $reference, 'models' => []];
            $scores = [];
            foreach ($models as $model) {
                if (!isset($warm[$model])) { // the first call loads the model; do not time it
                    $this->ocr->recognize($bytes, $image->mimeType, $model);
                    $warm[$model] = true;
                }
                $r = $this->ocr->recognize($bytes, $image->mimeType, $model);
                $row['models'][$model] = $r;
                $stats[$model]['n']++;
                if ($r['error']) {
                    $stats[$model]['errors']++;
                    continue;
                }
                $stats[$model]['ms'] += $r['ms'];
                $stats[$model]['chars'] += $r['chars'];
                $stats[$model]['lines'] += $r['lines'];
                if (null !== $reference && '' !== trim($reference)) {
                    $recall = self::wordRecall($reference, $r['text']);
                    $sim = self::similarity($reference, $r['text']);
                    $stats[$model]['refN']++;
                    $stats[$model]['recall'] += $recall;
                    $stats[$model]['sim'] += $sim;
                    $scores[$model] = $recall;
                    $row['models'][$model] += ['recall' => round($recall, 3), 'similarity' => round($sim, 3)];
                }
            }
            if (count($scores) === count($models) && count($models) > 1) {
                arsort($scores);
                $best = array_key_first($scores);
                $second = array_values($scores)[1];
                ++$wins[$scores[$best] - $second > 0.05 ? $best : 'tie'];
            }
            if ($out) {
                fwrite($out, json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
            }
            $this->em->clear();
            if (++$done % 25 === 0) {
                $io->writeln(sprintf('  … %d images', $done));
            }
            if ($limit > 0 && $done >= $limit) {
                break;
            }
        }

        $rows = [];
        foreach ($stats as $model => $s) {
            $ok = max(1, $s['n'] - $s['errors']);
            $rows[] = [$model, $s['n'], $s['errors'], round($s['ms'] / $ok), round($s['chars'] / $ok), sprintf('%.1f', $s['lines'] / $ok), $s['refN'],
                $s['refN'] ? sprintf('%.3f', $s['recall'] / $s['refN']) : '-', $s['refN'] ? sprintf('%.3f', $s['sim'] / $s['refN']) : '-'];
        }
        $io->table(['model', 'images', 'errors', 'mean ms', 'mean chars', 'mean lines', 'vs reference', 'word recall', 'char similarity'], $rows);
        $io->writeln('Head to head on word recall (a model wins by more than 0.05): '.json_encode($wins));

        return Command::SUCCESS;
    }

    /** @return list<string> */
    private static function words(string $text): array
    {
        return preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /** Share of the reference's words (3+ characters) that appear in the hypothesis, regardless of order. */
    private static function wordRecall(string $reference, string $hypothesis): float
    {
        $ref = array_values(array_filter(self::words($reference), static fn (string $w): bool => mb_strlen($w) >= 3));
        if (!$ref) {
            return 1.0;
        }
        $have = array_count_values(self::words($hypothesis));
        $hit = 0;
        foreach ($ref as $w) {
            if (($have[$w] ?? 0) > 0) {
                ++$hit;
                --$have[$w];
            }
        }

        return $hit / count($ref);
    }

    /** 1 - normalized edit distance over the words joined by single spaces. */
    private static function similarity(string $a, string $b): float
    {
        $a = implode(' ', self::words($a));
        $b = implode(' ', self::words($b));
        $max = max(strlen($a), strlen($b));

        return 0 === $max ? 1.0 : 1 - levenshtein($a, $b) / $max;
    }
}
