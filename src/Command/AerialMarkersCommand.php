<?php

declare(strict_types=1);
namespace App\Command;

use App\Dto\AerialLayout;
use Symfony\AI\Platform\Message\Content\Image;
use Symfony\AI\Platform\Message\{Message, MessageBag};
use Symfony\AI\Platform\PlatformInterface;
use Symfony\Component\Console\Attribute\{Argument, AsCommand, Option};
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Asks Gemini to box every grave marker in a straight-down drone image.
 * Gemini's detection convention is box_2d = [ymin, xmin, ymax, xmax] on a 0-1000 grid.
 */
#[AsCommand('app:aerial-markers', 'Detect grave markers in a top-down drone image with Gemini')]
final class AerialMarkersCommand
{
    private const PROMPT = 'This is a straight-down drone photograph of a churchyard cemetery. Find every grave marker: headstones, flat or flush markers, footstones, monuments and benches. Tall stones lean outward from the image centre because of the camera perspective; box the whole visible stone, not its shadow. Do not box people, cars, flowers, flags or patches of bare soil. For each marker give box2d as [ymin, xmin, ymax, xmax] normalized to 0-1000, a short form (upright, flat, slant, monument, footstone, other) and its main color. Then give the number of rows you can see and their direction in the image (for example "top-left to bottom-right"). List anything you are unsure about in uncertainties.';

    public function __construct(
        #[Autowire(service: 'ai.platform.gemini')] private readonly PlatformInterface $gemini,
    ) {}

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Top-down JPEG')] string $image,
        #[Option('Gemini model')] string $model = 'gemini-flash-lite-latest',
        #[Option('Annotated JPEG to write')] ?string $annotated = null,
        #[Option('JSON file to write')] ?string $json = null,
    ): int {
        $started = microtime(true);
        $result = $this->gemini->invoke($model,
            new MessageBag(Message::forSystem(self::PROMPT), Message::ofUser('Find the grave markers.', Image::fromFile($image))),
            ['response_format' => AerialLayout::class, 'temperature' => 0.1]);
        /** @var AerialLayout $layout */
        $layout = $result->asObject(AerialLayout::class);
        $seconds = microtime(true) - $started;

        $forms = array_count_values(array_map(static fn ($m) => $m->form, $layout->markers));
        arsort($forms);
        $io->writeln(sprintf('%d markers, %d rows (%s), %.1f s, model %s', \count($layout->markers), $layout->rows, $layout->rowDirection, $seconds, $model));
        $io->writeln('Forms: '.implode(', ', array_map(static fn ($k, $v) => "$k $v", array_keys($forms), $forms)));
        foreach ($layout->uncertainties as $u) {
            $io->writeln(' - '.$u);
        }

        if ($json) {
            file_put_contents($json, json_encode(['model' => $model, 'image' => basename($image), 'seconds' => round($seconds, 1), 'layout' => $layout], \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR));
        }
        if ($annotated) {
            $this->annotate($image, $layout, $annotated);
            $io->writeln('Annotated: '.$annotated);
        }

        return 0;
    }

    private function annotate(string $source, AerialLayout $layout, string $target): void
    {
        $img = imagecreatefromjpeg($source);
        [$w, $h] = [imagesx($img), imagesy($img)];
        $red = imagecolorallocate($img, 255, 40, 40);
        imagesetthickness($img, max(2, intdiv($w, 600)));
        foreach ($layout->markers as $i => $m) {
            [$y0, $x0, $y1, $x1] = array_map(static fn ($v) => (int) $v, $m->box2d + [0, 0, 0, 0]);
            imagerectangle($img, intdiv($x0 * $w, 1000), intdiv($y0 * $h, 1000), intdiv($x1 * $w, 1000), intdiv($y1 * $h, 1000), $red);
            imagestring($img, 5, intdiv($x0 * $w, 1000), max(0, intdiv($y0 * $h, 1000) - 16), (string) ($i + 1), $red);
        }
        imagejpeg($img, $target, 88);
    }
}
