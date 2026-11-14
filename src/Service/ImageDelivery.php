<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Image;
use App\Image\ImagePreset;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\UX\Image\ImageUrlGenerator;

final readonly class ImageDelivery
{
    public function __construct(
        private ImageUrlGenerator $urls,
        #[Autowire('%kernel.environment%')] private string $environment,
        #[Autowire('%kernel.project_dir%/var/media')] private string $mediaDirectory,
    ) {}

    public function url(Image $image, ImagePreset $preset): string
    {
        $source = $image->getThumbnailSource();
        if ($this->environment !== 'prod') {
            if (is_file($this->mediaDirectory.'/'.$image->storagePath)) {
                $source = $image->storagePath;
            } elseif (!str_starts_with($source, 'https://')) {
                throw new \RuntimeException(sprintf('Image %s has no local original or public HTTPS source for stock Imagor.', $image->id));
            }
        }

        return $this->generate($source, $preset);
    }

    public function storedDetail(string $storagePath, string $publicUrl): string
    {
        return $this->generate($this->environment === 'prod' ? $publicUrl : $storagePath, ImagePreset::Detail);
    }

    private function generate(string $source, ImagePreset $preset): string
    {
        $url = $this->urls->generate($source, preset: $preset->value);
        if ($this->environment === 'prod'
            && (parse_url($url, PHP_URL_HOST) !== 'imagor.survos.com'
                || parse_url($url, PHP_URL_SCHEME) !== 'https'
                || str_contains($url, '/unsafe/'))) {
            throw new \LogicException('Production UX_IMAGE_DSN must use signed HTTPS on imagor.survos.com.');
        }

        return $url;
    }

    public function thumbnail(Image $image): string
    {
        return $this->url($image, ImagePreset::Thumb);
    }

    public function detail(Image $image): string
    {
        return $this->url($image, ImagePreset::Detail);
    }

    public function origin(): string
    {
        $parts = parse_url($this->urls->generate('connection', preset: ImagePreset::Thumb->value));
        return $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
    }
}
