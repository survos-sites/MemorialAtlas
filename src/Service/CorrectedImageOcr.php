<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Platform\Message\Content\Image;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class CorrectedImageOcr
{
    public function __construct(#[Autowire(service: 'ai.platform.mistral')] private PlatformInterface $platform) {}

    public function recognize(string $path, array $frame): array
    {
        $result = $this->platform->invoke('mistral-ocr-latest', Image::fromFile($path), ['include_blocks' => true])->getResult();
        return self::reading($result->getRawResult()->getData(), $frame);
    }

    public static function reading(array $raw, array $frame): array
    {
        $page = $raw['pages'][0];
        if ($page['dimensions']['width'] !== $frame['width'] || $page['dimensions']['height'] !== $frame['height']) {
            throw new \RuntimeException('OCR returned a different image frame; its boxes were not applied.');
        }
        $lines = [];
        // Mistral supplies block geometry; retain whole blocks rather than invent line boxes.
        foreach ($page['blocks'] ?? [] as $i => $block) {
            if (!isset($block['content']) || !is_string($block['content'])) continue;
            $left = $block['top_left_x']; $top = $block['top_left_y'];
            $right = $block['bottom_right_x']; $bottom = $block['bottom_right_y'];
            foreach ([$left, $top, $right, $bottom] as $coordinate) {
                if (!is_numeric($coordinate) || !is_finite((float) $coordinate)) throw new \RuntimeException('Invalid OCR coordinate.');
            }
            if ($left < 0 || $top < 0 || $right > $frame['width'] || $bottom > $frame['height'] || $right <= $left || $bottom <= $top) throw new \RuntimeException('OCR returned coordinates outside the processed image.');
            $lines[] = ['id' => 'block-'.($i + 1), 'text' => $block['content'], 'polygon' => [['x'=>$left, 'y'=>$top], ['x'=>$right, 'y'=>$top], ['x'=>$right, 'y'=>$bottom], ['x'=>$left, 'y'=>$bottom]], 'confidence' => null];
        }
        return ['id' => bin2hex(random_bytes(12)), 'frame' => $frame, 'provider' => 'mistral', 'model' => $raw['model'], 'text' => $page['markdown'], 'lines' => $lines, 'geometry' => 'blocks', 'createdAt' => (new \DateTimeImmutable())->format(DATE_ATOM), 'receipt' => $raw];
    }
}
