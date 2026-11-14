<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Image;
use AsyncAws\S3\S3Client;
use Survos\ImagorBundle\Service\ImagorUrlBuilder;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class PhotoMetadata
{
    public function __construct(
        private MediaStorage $storage,
        private ImagorUrlBuilder $imagor,
        private HttpClientInterface $http,
        #[Autowire('%env(bool:SCANFONY_META_OCR)%')] private bool $metaOcr,
        #[Autowire('%env(S3_ENDPOINT)%')] private string $endpoint,
        #[Autowire('%env(AWS_S3_BUCKET_NAME)%')] private string $bucket,
        #[Autowire('%env(AWS_S3_ACCESS_ID)%')] private string $accessKey,
        #[Autowire('%env(AWS_S3_ACCESS_SECRET)%')] private string $secret,
    ) {}

    public function prepare(Image $media): void
    {
        $path = $this->storage->path($media);
        if ($media->sourceUrl === null) {
            $key = 'scanfony/originals/'.$media->sha256;
            $media->sourceUrl = $this->upload($path, $key, $media->mimeType);
        }
        $rendition = $media->imageMetadata['ocrRendition'] ?? null;
        if ($rendition !== null && $media->ocrText === null) {
            $ocrPath = $this->storage->renditionPath($rendition['storagePath']);
            if (!is_file($ocrPath) || hash_file('sha256', $ocrPath) !== $rendition['sha256']) throw new \RuntimeException('OCR rendition is missing or changed.');
            $rendition['sourceUrl'] ??= $this->upload($ocrPath, 'scanfony/ocr/'.$rendition['sha256'].'.jpg', 'image/jpeg');
            $rendition['url'] = $this->imagor->preset($rendition['sourceUrl'], 'large');
            $media->imageMetadata['ocrRendition'] = $rendition;
        }
        $requestOcr = $this->metaOcr && $media->ocrText === null;
        if (!isset($media->imageMetadata['imagor']) || ($requestOcr && !isset($media->imageMetadata['imagor']['ocr']))) {
            $meta = $this->http->request('GET', $this->imagor->metaUrl($media->sourceUrl, $requestOcr && $rendition === null ? ['thumbhash', 'blurhash', 'avgcolor', 'ocr'] : ['thumbhash', 'blurhash', 'avgcolor']), ['timeout' => 120, 'max_duration' => 180])->toArray();
            if (empty($meta['width']) || empty($meta['height'])) {
                throw new \RuntimeException('Imagor did not return valid image dimensions.');
            }
            if ($requestOcr && $rendition !== null) {
                $ocrMeta = $this->http->request('GET', $this->imagor->metaUrl($rendition['sourceUrl'], ['ocr']), ['timeout' => 120, 'max_duration' => 180])->toArray();
                $meta['ocr'] = $ocrMeta['ocr'] ?? null;
            }
            if ($requestOcr && !is_string($meta['ocr']['text'] ?? null)) throw new \RuntimeException('Imagor OCR was requested but no text was returned. Check the RustO build or disable SCANFONY_META_OCR.');
            $media->imageMetadata['imagor'] = $meta;
        }
        if ($this->metaOcr && $media->ocrText === null && isset($media->imageMetadata['imagor']['ocr'])) {
            $media->ocrText = $media->imageMetadata['imagor']['ocr']['text'];
            $media->aiResults['ocr'] = ['provider'=>'imagor', 'raw'=>$media->imageMetadata['imagor']['ocr']];
        }
        // Imagor autorotates and drops Orientation, so keep the camera's own EXIF from the original bytes.
        if (!isset($media->imageMetadata['exif'])) {
            $media->imageMetadata['exif'] = self::readExif($path);
        }
        CaptureMetadata::apply($media, $media->imageMetadata['imagor']['exif'] ?? [], 'Imagor EXIF GPS');
        CaptureMetadata::apply($media, $media->imageMetadata['exif'], 'Original EXIF GPS');
        // The large preset keeps weathered lettering legible; the 512px observe preset is too small here.
        $media->imageUrl = $this->imagor->preset($media->sourceUrl, 'large');
    }

    public function upload(string $path, string $key, string $mimeType): string
    {
        $s3 = new S3Client(['endpoint' => $this->endpoint, 'region' => 'fsn1', 'pathStyleEndpoint' => true, 'accessKeyId' => $this->accessKey, 'accessKeySecret' => $this->secret]);
        $stream = fopen($path, 'rb');
        try {
            $s3->putObject(['Bucket' => $this->bucket, 'Key' => $key, 'Body' => $stream, 'ContentType' => $mimeType])->resolve();
        } finally {
            fclose($stream);
        }
        return 's3://'.$this->bucket.'/'.$key;
    }

    /**
     * Every readable EXIF tag of the file as written by the camera, JSON-safe: binary blobs (MakerNote,
     * thumbnails) are dropped. Orientation is the camera's 1-8 value; Imagor reports 0 because it has
     * already rotated the pixels. Returns [] when the file or the exif extension is unavailable.
     *
     * @return array<string, mixed>
     */
    public static function readExif(string $path): array
    {
        if (!function_exists('exif_read_data') || !is_file($path)) {
            return [];
        }
        $raw = @exif_read_data($path, null, false);
        if (!is_array($raw)) {
            return [];
        }
        $clean = static function (mixed $value) use (&$clean): mixed {
            if (is_array($value)) {
                $out = [];
                foreach ($value as $k => $v) {
                    $v = $clean($v);
                    if ($v !== null) $out[$k] = $v;
                }
                return $out ?: null;
            }
            if (is_string($value)) {
                return mb_check_encoding($value, 'UTF-8') && !preg_match('/[\x00-\x08\x0E-\x1F]/', $value) ? $value : null;
            }
            return is_scalar($value) ? $value : null;
        };
        $out = [];
        foreach ($raw as $tag => $value) {
            if (in_array($tag, ['MakerNote', 'UserComment', 'THUMBNAIL', 'FileName', 'FileDateTime', 'FileSize', 'FileType', 'MimeType', 'SectionsFound', 'COMPUTED'], true)) continue;
            $value = $clean($value);
            if ($value !== null) $out[$tag] = $value;
        }
        return $out;
    }

    public static function coordinate(array $parts, string $reference, int $max): ?float
    {
        if (count($parts) !== 3 || !in_array($reference, $max === 90 ? ['N','S'] : ['E','W'], true)) {
            return null;
        }
        $numbers = [];
        foreach ($parts as $part) {
            $fraction = explode('/', (string) $part, 2);
            if (!is_numeric($fraction[0]) || (isset($fraction[1]) && (!is_numeric($fraction[1]) || (float) $fraction[1] === 0.0))) return null;
            $numbers[] = (float) $fraction[0] / (isset($fraction[1]) ? (float) $fraction[1] : 1);
        }
        if ($numbers[0] < 0 || $numbers[1] < 0 || $numbers[1] >= 60 || $numbers[2] < 0 || $numbers[2] >= 60) return null;
        $value = $numbers[0] + $numbers[1] / 60 + $numbers[2] / 3600;
        return $value > $max ? null : $value * (in_array($reference, ['S','W'], true) ? -1 : 1);
    }
}
