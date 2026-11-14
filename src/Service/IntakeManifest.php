<?php

declare(strict_types=1);

namespace App\Service;

/** A local handoff from SSAI, not a claim about which photographs share a marker. */
final class IntakeManifest
{
    public static function load(string $manifestPath, string $directory): array
    {
        $manifest = json_decode(file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
        if (($manifest['version'] ?? null) !== 1 || ($manifest['provider'] ?? null) !== 'ssai'
            || !is_string($manifest['tenantCode'] ?? null) || !is_string($manifest['intakeCode'] ?? null)
            || !is_array($manifest['images'] ?? null) || $manifest['images'] === []) {
            throw new \InvalidArgumentException('Invalid SSAI intake manifest.');
        }
        $root = realpath($directory);
        if ($root === false) throw new \InvalidArgumentException('Intake directory is missing.');
        $seen = [];
        foreach ($manifest['images'] as &$image) {
            if (!is_string($image['imageId'] ?? null) || !is_string($image['originalName'] ?? null)
                || !is_int($image['sequence'] ?? null) || $image['sequence'] < 1
                || isset($seen[$image['imageId']])) throw new \InvalidArgumentException('Invalid or duplicate intake image.');
            $seen[$image['imageId']] = true;
            foreach (['path' => 'sha256', 'ocrPath' => 'ocrSha256'] as $pathKey => $hashKey) {
                $relative = $image[$pathKey] ?? null;
                $hash = $image[$hashKey] ?? null;
                if (!is_string($relative) || !is_string($hash) || !preg_match('/^[a-f0-9]{64}$/D', $hash)) throw new \InvalidArgumentException('Invalid intake file reference.');
                $absolute = realpath($root.'/'.$relative);
                if ($absolute === false || !str_starts_with($absolute, $root.DIRECTORY_SEPARATOR) || !is_file($absolute)
                    || hash_file('sha256', $absolute) !== $hash) throw new \InvalidArgumentException('Intake file missing, outside directory, or checksum mismatch: '.$relative);
                $image[$pathKey] = $absolute;
            }
        }
        unset($image);
        usort($manifest['images'], static fn (array $a, array $b): int => $a['sequence'] <=> $b['sequence']);
        return $manifest;
    }
}
