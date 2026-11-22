<?php

declare(strict_types=1);

namespace App\Service;

use App\Decision\ImageQuestions;
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

        // Only cards turn the original: the record page draws OCR boxes in the camera's frame.
        return $this->generate($source, $preset, $preset === ImagePreset::Thumb ? $this->turn($image, 'original') : 0);
    }

    /** The AI-cleaned display copy, or null when the photo has none (or dev lacks the local copy). */
    public function cleaned(Image $image, ImagePreset $preset, bool $rotated = true): ?string
    {
        $view = $image->imageMetadata[ImageCleaner::KEY] ?? null;
        if (!is_array($view)) return null;
        $turn = $rotated ? $this->turn($image, 'cleaned') : 0;
        if ($this->environment === 'prod') return $this->generate(substr($view['sourceUrl'], strlen('s3://')), $preset, $turn);

        return is_file($this->mediaDirectory.'/'.$view['storagePath']) ? $this->generate($view['storagePath'], $preset, $turn) : null;
    }

    public function storedDetail(string $storagePath, string $publicUrl): string
    {
        return $this->generate($this->environment === 'prod' ? $publicUrl : $storagePath, ImagePreset::Detail);
    }

    /** Jev's counter-clockwise turn for the original or the cleaned copy (app:images:orient); 0 when not assessed. */
    private function turn(Image $image, string $view): int
    {
        return (int) ($image->aiResults[ImageQuestions::ORIENTATION_RUN][$view]['turn'] ?? 0);
    }

    private function generate(string $source, ImagePreset $preset, int $turn = 0): string
    {
        $url = $this->urls->generate($source, operations: $turn === 0 ? [] : ['imagor' => ['rotate' => $turn]], preset: $preset->value);
        if ($this->environment === 'prod'
            && (parse_url($url, PHP_URL_HOST) !== 'imagor.survos.com'
                || parse_url($url, PHP_URL_SCHEME) !== 'https'
                || str_contains($url, '/unsafe/'))) {
            throw new \LogicException('Production UX_IMAGE_DSN must use signed HTTPS on imagor.survos.com.');
        }

        return $url;
    }

    /**
     * The thumbnail that search cards and the chat show. The AI-cleaned copy is a small file on S3;
     * the original is a full-size photo on Hugging Face, which Imagor takes 30 to 50 seconds to resize the first time.
     *
     * @return array{url: string, aiCleaned: bool}
     */
    public function card(Image $image): array
    {
        $cleaned = $this->cleaned($image, ImagePreset::Thumb);

        return ['url' => $cleaned ?? $this->thumbnail($image), 'aiCleaned' => $cleaned !== null];
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
