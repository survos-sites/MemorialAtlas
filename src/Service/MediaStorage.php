<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\{Item, Media, Image, Audio};
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

final class MediaStorage
{
    public function __construct(#[Autowire('%kernel.project_dir%/var/media')] private string $directory) {}

    public function import(Item $item, string $path, ?string $name = null): Media
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \InvalidArgumentException('Cannot read media file.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        $kind = match (true) {
            in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'], true) => Media::IMAGE,
            in_array($mime, ['audio/mpeg', 'audio/mp4', 'audio/ogg', 'audio/webm', 'audio/wav', 'audio/x-wav', 'video/webm'], true) => Media::AUDIO,
            default => throw new \InvalidArgumentException(sprintf('Unsupported media type: %s', $mime)),
        };
        $hash = hash_file('sha256', $path);
        $relative = substr($hash, 0, 2).'/'.$hash;
        $filesystem = new Filesystem();
        $filesystem->mkdir(dirname($this->directory.'/'.$relative));
        $filesystem->copy($path, $this->directory.'/'.$relative);
        $position = 0;
        foreach ($item->media as $existing) {
            $position = max($position, $existing->position + 1);
        }
        $class = $kind === Media::IMAGE ? Image::class : Audio::class;
        return new $class($item, $position, mb_substr(basename($name ?? $path), 0, 255), $relative, $mime, $hash);
    }

    public function importOcrRendition(string $path, string $sha256): array
    {
        if (hash_file('sha256', $path) !== $sha256 || (new \finfo(FILEINFO_MIME_TYPE))->file($path) !== 'image/jpeg') {
            throw new \InvalidArgumentException('Invalid OCR rendition.');
        }
        $relative = 'ocr/'.substr($sha256, 0, 2).'/'.$sha256.'.jpg';
        $filesystem = new Filesystem();
        $filesystem->mkdir(dirname($this->directory.'/'.$relative));
        $filesystem->copy($path, $this->directory.'/'.$relative);
        return ['storagePath' => $relative, 'sha256' => $sha256];
    }

    public function renditionPath(string $relative): string
    {
        if (!preg_match('#^ocr/[a-f0-9]{2}/[a-f0-9]{64}\\.jpg$#D', $relative)) throw new \InvalidArgumentException('Invalid rendition path.');
        return $this->directory.'/'.$relative;
    }

    public function path(Media $media): string
    {
        return $this->directory.'/'.$media->storagePath;
    }
}
