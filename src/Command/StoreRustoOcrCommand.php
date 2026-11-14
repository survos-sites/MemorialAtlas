<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Image;
use App\Service\RustoOcr;
use Doctrine\ORM\EntityManagerInterface;
use Survos\ImagorBundle\Service\ImagorUrlBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Stores the rusto OCR reading of every photograph, one per model, in `aiResults['rusto'][<model>]`:
 * the lines with their boxes, the blocks, the text angle and the timing. The photo goes to the service as a
 * signed full-size imagor URL, so Imagor applies the EXIF rotation and the service fetches it directly.
 * Existing readings are kept unless --force is given, so an interrupted run can be repeated.
 */
#[AsCommand(name: 'app:ocr:store', description: 'Store rusto OCR readings (tiny, small) on every photograph.')]
final class StoreRustoOcrCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RustoOcr $ocr,
        private readonly ImagorUrlBuilder $imagor,
        private readonly HttpClientInterface $http,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('models', null, InputOption::VALUE_REQUIRED, 'Comma-separated models', 'tiny,small')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Stop after this many readings', '0')
            ->addOption('media', null, InputOption::VALUE_REQUIRED, 'Only this media id')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Replace readings that already exist')
            ->addOption('imagor', null, InputOption::VALUE_NONE, 'Read through imagor ocr() (one call, 960 px cap) instead of the Mistral endpoint');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $models = array_values(array_filter(array_map('trim', explode(',', (string) $input->getOption('models')))));
        $limit = (int) $input->getOption('limit');
        $force = (bool) $input->getOption('force');
        $only = $input->getOption('media');
        $viaImagor = (bool) $input->getOption('imagor');

        $stored = $failed = $seen = 0;
        foreach ($this->em->getRepository(Image::class)->findBy($only ? ['id' => (int) $only] : [], ['id' => 'ASC']) as $image) {
            if ($image->sourceUrl === null) {
                continue;
            }
            ++$seen;
            $url = $this->imagor->build($image->sourceUrl, 'fit-in/2400x2400/filters:quality(92)');
            foreach ($models as $model) {
                if (!$force && isset($image->aiResults['rusto'][$model]['page'])) {
                    continue;
                }
                $result = $viaImagor ? $this->imagorReading($image->sourceUrl, $model) : $this->ocr->recognizeUrl($url, $model);
                if ($result['error'] !== null) {
                    ++$failed;
                    $io->warning(sprintf('#%d %s: %s', $image->id, $model, $result['error']));
                    continue;
                }
                $page = $result['page'];
                $image->aiResults['rusto'][$model] = [
                    'at' => (new \DateTimeImmutable())->format(DATE_ATOM),
                    'ms' => $result['ms'],
                    'source' => $viaImagor ? sprintf('imagor ocr(%s), 960 px cap', $model) : 'imagor fit-in 2400x2400, quality 92',
                    'page' => [
                        'markdown' => $page['markdown'] ?? '',
                        'dimensions' => $page['dimensions'] ?? null,
                        'lines' => $page['lines'] ?? [],
                        'blocks' => $page['blocks'] ?? [],
                        'text_angle' => $page['text_angle'] ?? null,
                        'orientation_corrected' => $page['orientation_corrected'] ?? null,
                    ],
                ];
                ++$stored;
                $io->writeln(sprintf('#%d %s: %d lines, %d ms', $image->id, $model, count($page['lines'] ?? []), $result['ms']));
                if ($stored % 5 === 0) {
                    $this->em->flush();
                }
                if ($limit > 0 && $stored >= $limit) {
                    break 2;
                }
            }
        }
        $this->em->flush();
        $io->success(sprintf('%d readings stored for %d photographs; %d failed.', $stored, $seen, $failed));

        return $failed > 0 && $stored === 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * One signed imagor /meta call that reads the photo itself; the page has the same shape as the endpoint's
     * (lines with boxes, no blocks).
     *
     * @return array{model: string, ms: int, page: array<string, mixed>, error: ?string}
     */
    private function imagorReading(string $sourceUrl, string $model): array
    {
        $started = hrtime(true);
        $url = $this->imagor->build($sourceUrl, 'meta/filters:ocr('.($model === 'tiny' ? '' : $model).')');
        try {
            $ocr = $this->http->request('GET', $url, ['timeout' => 120])->toArray()['ocr'] ?? throw new \RuntimeException('imagor returned no ocr object (is OCR_ENABLED on?)');
            $page = [
                'markdown' => (string) ($ocr['text'] ?? ''),
                'dimensions' => ['width' => $ocr['width'] ?? null, 'height' => $ocr['height'] ?? null],
                'lines' => array_map(static fn (array $r): array => ['text' => $r['text'], 'score' => $r['score'], 'box' => $r['box']], $ocr['regions'] ?? []),
                'blocks' => [],
            ];

            return ['model' => $model, 'ms' => (int) round((hrtime(true) - $started) / 1e6), 'page' => $page, 'error' => null];
        } catch (\Throwable $e) {
            return ['model' => $model, 'ms' => (int) round((hrtime(true) - $started) / 1e6), 'page' => [], 'error' => $e->getMessage()];
        }
    }
}
