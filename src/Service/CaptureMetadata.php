<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Image;

final class CaptureMetadata
{
    public static function apply(Image $image, array $exif, string $source): void
    {
        [$latitude, $longitude] = self::coordinates($exif);
        if ($latitude !== null && $longitude !== null) {
            $image->latitude = $latitude;
            $image->longitude = $longitude;
            $image->imageMetadata['capture']['coordinateSource'] = $source.' (camera position)';
        }
        if (isset($exif['DateTimeOriginal'])) $image->imageMetadata['capture']['dateTimeOriginal'] = $exif['DateTimeOriginal'];
    }

    /** @return array{?float, ?float} */
    public static function coordinates(array $exif): array
    {
        $coordinate = static function (string $key, int $max) use ($exif): ?float {
            $parts = $exif[$key] ?? [];
            if (is_string($parts)) $parts = preg_split('/\s+/', trim($parts));
            $reference = $exif[$key.'Ref'] ?? '';
            return is_array($parts) && is_string($reference) ? PhotoMetadata::coordinate($parts, trim($reference), $max) : null;
        };
        return [$coordinate('GPSLatitude', 90), $coordinate('GPSLongitude', 180)];
    }
}
