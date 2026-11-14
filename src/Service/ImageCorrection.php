<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Image;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class ImageCorrection
{
    public const string KEY = 'perspectiveCorrections';

    public function __construct(
        private PerspectiveCorrector $corrector,
        private ImageDelivery $delivery,
        private HttpClientInterface $http,
        private CorrectedImageOcr $ocr,
        #[Autowire('%kernel.project_dir%/var/media')] private string $directory,
    ) {}

    public function create(Image $image, array $corners): string
    {
        // Snapshot the same oriented, bounded detail frame used by the corner editor.
        $bytes = $this->http->request('GET', $this->delivery->detail($image), ['timeout' => 30])->getContent();
        $result = $this->corrector->correct($bytes, $corners);
        $parent = $this->store($bytes, 'webp');
        $file = $this->store($result['bytes'], 'jpg');
        $id = hash('sha256', json_encode([$parent['sha256'], $result['sourceCorners'], $file['sha256']], JSON_THROW_ON_ERROR));
        $size = getimagesizefromstring($bytes);
        $parentFrame = ['id' => $parent['sha256'], 'source' => $parent['storagePath'], 'width' => $size[0], 'height' => $size[1], 'sha256' => $parent['sha256']];
        $frame = ['id' => $file['sha256'], 'source' => $file['storagePath'], 'width' => $result['width'], 'height' => $result['height'], 'sha256' => $file['sha256'], 'perspective' => ['parentFrameId' => $parentFrame['id'], 'sourceCorners' => $result['sourceCorners']]];
        if (!isset($image->imageMetadata[self::KEY][$id])) {
            $image->imageMetadata[self::KEY][$id] = ['id' => $id, 'schemaVersion' => '1.0', 'originalSha256' => $image->sha256, 'parentFrame' => $parentFrame, 'frame' => $frame, 'createdAt' => (new \DateTimeImmutable())->format(DATE_ATOM), 'readings' => []];
        }
        $image->imageMetadata['activeCorrection'] = $id;
        return $id;
    }

    public function recognize(Image $image, string $id): void
    {
        $correction = $this->get($image, $id);
        $frame = $correction['frame'];
        $reading = $this->ocr->recognize($this->path($frame['source']), $frame);
        $image->imageMetadata[self::KEY][$id]['readings'][] = $reading;
    }

    public function get(Image $image, string $id): array
    {
        return $image->imageMetadata[self::KEY][$id] ?? throw new \InvalidArgumentException('Unknown correction.');
    }

    public function path(string $source): string
    {
        if (!preg_match('~^corrections/[a-f0-9]{64}\.(jpg|webp)$~D', $source)) throw new \InvalidArgumentException('Invalid correction source.');
        return $this->directory.'/'.$source;
    }

    private function store(string $bytes, string $extension): array
    {
        $hash = hash('sha256', $bytes);
        $relative = 'corrections/'.$hash.'.'.$extension;
        $filesystem = new Filesystem();
        $filesystem->mkdir($this->directory.'/corrections');
        if (!is_file($this->path($relative))) $filesystem->dumpFile($this->path($relative), $bytes);
        return ['storagePath' => $relative, 'sha256' => $hash];
    }
}
