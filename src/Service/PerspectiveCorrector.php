<?php

declare(strict_types=1);

namespace App\Service;

final class PerspectiveCorrector
{
    public static function enabled(?bool $correction = null): bool
    {
        $correction ??= true;
        if ($correction && !extension_loaded('opencv')) {
            throw new \RuntimeException('Install the OpenCV extension or run this with --no-correction.');
        }
        return $correction;
    }

    /** @return array{bytes: string, width: int, height: int, sourceCorners: array} */
    public function correct(string $bytes, array $corners): array
    {
        self::enabled();
        $size = getimagesizefromstring($bytes);
        if (false === $size || $size[0] * $size[1] > 4_000_000) {
            throw new \InvalidArgumentException('Use the bounded detail image as the correction source.');
        }
        $points = $this->points($corners, $size[0], $size[1]);
        $distance = static fn (array $a, array $b): float => hypot($a[0] - $b[0], $a[1] - $b[1]);
        $width = max($distance($points[0], $points[1]), $distance($points[3], $points[2]));
        $height = max($distance($points[0], $points[3]), $distance($points[1], $points[2]));
        $scale = min(1.0, 1600 / max($width, $height));
        $width = (int) round($width * $scale);
        $height = (int) round($height * $scale);
        if (min($width, $height) < 32) {
            throw new \InvalidArgumentException('Select a larger inscription area.');
        }
        $source = \CV\Mat::fromArray($points, \CV\CV_32F);
        $destination = \CV\Mat::fromArray([[0.0, 0.0], [$width - 1.0, 0.0], [$width - 1.0, $height - 1.0], [0.0, $height - 1.0]], \CV\CV_32F);
        $matrix = \CV\getPerspectiveTransform($source, $destination);
        $image = \CV\imdecode($bytes, \CV\IMREAD_COLOR);
        $corrected = \CV\warpPerspective($image, $matrix, [$width, $height]);
        [$ok, $encoded] = \CV\imencode('.jpg', $corrected, [\CV\IMWRITE_JPEG_QUALITY, 95]);
        if (!$ok) throw new \RuntimeException('OpenCV could not encode the corrected image.');
        return ['bytes' => $encoded, 'width' => $width, 'height' => $height, 'sourceCorners' => array_map(static fn (array $p): array => ['x' => $p[0], 'y' => $p[1]], $points)];
    }

    /** Normalized UI coordinates, ordered TL, TR, BR, BL, converted to pixel centers. */
    public function points(array $corners, int $width, int $height): array
    {
        if (!array_is_list($corners) || count($corners) !== 4) throw new \InvalidArgumentException('Select four corners.');
        $points = [];
        foreach ($corners as $point) {
            if (!is_array($point) || !array_is_list($point) || count($point) !== 2) throw new \InvalidArgumentException('Each corner needs x and y.');
            foreach ($point as $coordinate) {
                if ((!is_int($coordinate) && !is_float($coordinate)) || !is_finite((float) $coordinate) || $coordinate < 0 || $coordinate > 1) throw new \InvalidArgumentException('Corners must lie inside the photograph.');
            }
            $points[] = [(float) $point[0] * ($width - 1), (float) $point[1] * ($height - 1)];
        }
        for ($i = 0; $i < 4; ++$i) {
            $a = $points[$i]; $b = $points[($i + 1) % 4]; $c = $points[($i + 2) % 4];
            $cross = ($b[0] - $a[0]) * ($c[1] - $b[1]) - ($b[1] - $a[1]) * ($c[0] - $b[0]);
            if ($cross <= 1) throw new \InvalidArgumentException('Select a convex area clockwise: top-left, top-right, bottom-right, bottom-left.');
        }
        if (($points[0][1] + $points[1][1]) >= ($points[2][1] + $points[3][1]) || ($points[0][0] + $points[3][0]) >= ($points[1][0] + $points[2][0])) {
            throw new \InvalidArgumentException('Start at the top-left and follow the labelled corner order.');
        }
        return $points;
    }
}
