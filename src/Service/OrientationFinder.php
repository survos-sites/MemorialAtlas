<?php

declare(strict_types=1);

namespace App\Service;

use App\Decision\ImageQuestions as Q;
use App\Decision\JevClient;
use App\Entity\Image;
use App\Image\ImagePreset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\{Argument, AsCommand, Option};
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Jev sees four turned copies of a photo in one call and says which one reads upright. Phones photographing
 * a flat marker from above often record the wrong EXIF orientation, so the cards showed those stones sideways.
 * The result is a display rotation; OCR and the record page's original keep the camera's frame.
 */
final readonly class OrientationFinder
{
    /** Below this, or without a clear lead over the photo as taken, the photo keeps its orientation. */
    public const MIN_UPRIGHT = 0.60;
    public const MIN_LEAD = 0.30;

    public function __construct(
        private JevClient $jev,
        private ImageDecision $decisions,
        private ImageDelivery $delivery,
        private EntityManagerInterface $em,
    ) {}

    /**
     * @param array<int, float> $upright the probability of "upright" for each counter-clockwise turn
     */
    public static function choose(array $upright): int
    {
        arsort($upright);
        $turn = array_key_first($upright);

        return $turn !== 0 && $upright[$turn] >= self::MIN_UPRIGHT && $upright[$turn] - ($upright[0] ?? 0.0) >= self::MIN_LEAD ? $turn : 0;
    }

    /** @return array{turn: int, upright: array<int, float>, model: string} */
    public function evaluate(string $url): array
    {
        $bitmap = imagecreatefromstring($this->decisions->decisionJpeg($url)) ?: throw new \RuntimeException('Cannot decode the photo.');
        $images = [];
        foreach (Q::TURNS as $turn) {
            $turned = $turn === 0 ? $bitmap : imagerotate($bitmap, $turn, 0);
            ob_start();
            imagejpeg($turned, null, 90);
            $images['turn'.$turn] = (string) ob_get_clean();
        }
        $run = $this->jev->evaluate([], Q::orientation(), images: $images);
        $upright = [];
        foreach (Q::TURNS as $turn) {
            $upright[$turn] = (float) ($run['answers']['turn'.$turn]['probabilities']['upright'] ?? 0);
        }

        return ['turn' => self::choose($upright), 'upright' => $upright, 'model' => $run['model'], 'usage' => $run['usage'], 'createdAt' => $run['createdAt']];
    }

    #[AsCommand('app:images:orient', 'Ask Jev which way each photo reads upright and store the display rotation')]
    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Image IDs; all photos when empty')] array $ids = [],
        #[Option('Ask again even when a result is stored')] bool $refresh = false,
    ): int {
        $repository = $this->em->getRepository(Image::class);
        $images = $ids === [] ? $repository->findBy([], ['id' => 'ASC']) : $repository->findBy(['id' => array_map('intval', $ids)], ['id' => 'ASC']);
        $rows = [];
        foreach ($images as $image) {
            if (!$refresh && isset($image->aiResults[Q::ORIENTATION_RUN])) continue;
            try {
                $result = ['original' => $this->evaluate($this->delivery->detail($image))];
                $cleaned = $this->delivery->cleaned($image, ImagePreset::Detail, rotated: false);
                if ($cleaned !== null) $result['cleaned'] = $this->evaluate($cleaned);
            } catch (\Throwable $e) {
                $io->warning(sprintf('Image %d: %s', $image->id, $e->getMessage()));
                continue;
            }
            $image->aiResults[Q::ORIENTATION_RUN] = $result;
            $this->em->flush();
            $rows[] = [$image->id, $result['original']['turn'], isset($result['cleaned']) ? $result['cleaned']['turn'] : '-',
                sprintf('%.2f', max($result['original']['upright']))];
        }
        $io->table(['Image', 'Original turn', 'Cleaned turn', 'Best upright'], $rows);
        $io->success(sprintf('%d photos checked. Run elastic:spool:flush to refresh search thumbnails.', count($rows)));

        return 0;
    }
}
