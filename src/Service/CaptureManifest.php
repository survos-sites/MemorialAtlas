<?php

declare(strict_types=1);

namespace App\Service;

final class CaptureManifest
{
    public static function load(string $path, string $directory): array
    {
        if (!is_file($path)) throw new \InvalidArgumentException('Manifest not found.');
        $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (($data['version'] ?? null) !== 1 || !is_array($data['groups'] ?? null) || $data['groups'] === []) {
            throw new \InvalidArgumentException('Expected version 1 and nonempty groups.');
        }
        foreach (['name', 'context', 'groupingMethod'] as $field) {
            if (!is_string($data[$field] ?? null) || trim($data[$field]) === '') throw new \InvalidArgumentException('Missing manifest '.$field.'.');
        }
        $seen = [];
        foreach ($data['groups'] as $group) {
            if (!is_array($group['files'] ?? null) || $group['files'] === [] || !is_string($group['basis'] ?? null)) {
                throw new \InvalidArgumentException('Every group needs files and a grouping basis.');
            }
            foreach ($group['files'] as $file) {
                if (!is_string($file) || $file !== basename($file) || str_contains($file, '\\') || isset($seen[$file])) {
                    throw new \InvalidArgumentException('Each filename must occur once and contain no directory.');
                }
                $seen[$file] = true;
                $source = $directory.'/'.$file;
                if (!is_file($source) || !is_string($data['sha256'][$file] ?? null) || !hash_equals($data['sha256'][$file], hash_file('sha256', $source))) {
                    throw new \InvalidArgumentException('Missing file or SHA-256 mismatch: '.$file);
                }
            }
        }
        return $data;
    }
}
