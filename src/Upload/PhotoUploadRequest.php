<?php

declare(strict_types=1);

namespace App\Upload;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[Exclude]
final readonly class PhotoUploadRequest
{
    public function __construct(
        #[Assert\NotBlank] public string $token,
        #[Assert\NotNull, Assert\Image(maxSize: '30M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'], maxPixels: 60_000_000, detectCorrupted: true)]
        public ?UploadedFile $photo = null,
        #[Assert\Positive] public ?int $cemeteryId = null,
        public ?bool $correction = null,
        public ?array $corners = null,
    ) {}

    public function normalizedCorners(): ?array
    {
        if ($this->corners === null) return null;
        return array_map(static fn ($point) => is_array($point)
            ? array_map(static fn ($value) => is_string($value) && is_numeric($value) ? (float) $value : $value, $point)
            : $point, $this->corners);
    }
}
