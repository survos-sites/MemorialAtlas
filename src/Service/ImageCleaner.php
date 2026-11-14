<?php

declare(strict_types=1);

namespace App\Service;

use App\Decision\{ImageQuestions as Q, JevClient};
use App\Entity\Image;
use Symfony\AI\Platform\Message\Content\Image as ImageContent;
use Symfony\AI\Platform\Message\{Message, MessageBag};
use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Platform\Result\{BinaryResult, MultiPartResult};
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Asks an image model for a straightened, evenly lit copy of a gravestone photo, for display only.
 * The model redraws pixels and can change letters, so the copy is never read by OCR, Jev or
 * extraction, and pages label it. The original photo stays the evidence.
 */
final readonly class ImageCleaner
{
    public const KEY = 'cleanedView';
    public const PROMPT = 'Edit this photograph of a gravestone. Crop to the stone, correct the perspective so the inscribed face is a straight-on rectangle, level it, and even out shadows and lighting. '
        .'Do not change, add, remove, sharpen into new shapes, or "fix" any letter, number, punctuation mark or symbol: every character must stay exactly as carved, including worn or unclear ones. Return only the edited image.';

    public function __construct(
        private JevClient $jev,
        private ImageDecision $decisions,
        private PhotoMetadata $metadata,
        private HttpClientInterface $http,
        #[Autowire(service: 'ai.platform.gemini')] private PlatformInterface $gemini,
        #[Autowire('%env(SCANFONY_CLEANUP_MODEL)%')] private string $model,
        #[Autowire('%env(bool:SCANFONY_CLEANUP_ENABLED)%')] private bool $enabled,
        #[Autowire('%kernel.project_dir%/var/media')] private string $directory,
    ) {}

    /** Only the main photo of a gravestone is worth cleaning; signs, documents and overviews are not. */
    public static function eligible(Image $image): bool
    {
        $answers = $image->aiResults[Q::RESULT]['answers'] ?? [];
        return ($answers[Q::TYPE]['choice'] ?? null) === 'gravestone' && ($answers[Q::ROLE]['choice'] ?? null) === 'subject';
    }

    public const MIN_ANGLED = 0.75;

    public static function wanted(array $jevRun): bool
    {
        $answer = $jevRun['answers'][Q::CLEANUP] ?? null;
        return is_array($answer) && $answer['choice'] === 'angled' && ($answer['probabilities']['angled'] ?? 0) >= self::MIN_ANGLED;
    }

    public function clean(Image $image, callable $progress): void
    {
        if (!$this->enabled || !self::eligible($image) || $image->imageUrl === null) return;
        $inputHash = hash('sha256', json_encode([$image->sha256, $image->imageUrl, $this->jev->model, Q::cleanup()[Q::CLEANUP], $this->model, self::PROMPT], JSON_THROW_ON_ERROR));
        if (($image->imageMetadata[self::KEY]['inputHash'] ?? null) === $inputHash) return;

        $run = $image->aiResults[Q::CLEANUP_RUN] ?? null;
        if (($run['inputHash'] ?? null) !== $inputHash) {
            $progress('Jev: does this photo need a cleaned view?');
            $run = $this->jev->evaluate(['ocrText' => $image->ocrText], Q::cleanup(), $this->decisions->decisionJpeg($image->imageUrl)) + ['inputHash' => $inputHash];
            $image->aiResults[Q::CLEANUP_RUN] = $run;
        }
        $answer = $run['answers'][Q::CLEANUP];
        $verdict = sprintf('%s, %.2f angled', $answer['choice'], $answer['probabilities']['angled'] ?? 0);
        if (!self::wanted($run)) {
            $progress('Jev: keep the photo as it is ('.$verdict.').');
            return;
        }
        $progress('Jev: photographed at an angle ('.$verdict.').');

        $progress('Gemini: straighten and relight a display copy');
        $source = $this->http->request('GET', $image->imageUrl, ['max_duration' => 60])->getContent();
        $mime = getimagesizefromstring($source)['mime'] ?? 'image/jpeg';
        $started = microtime(true);
        $result = $this->gemini->invoke($this->model, new MessageBag(Message::ofUser(self::PROMPT, new ImageContent($source, $mime))))->getResult();
        $output = null;
        foreach ($result instanceof MultiPartResult ? $result->getContent() : [$result] as $part) {
            if ($part instanceof BinaryResult) { $output = $part; break; }
        }
        if ($output === null) throw new \RuntimeException('The image model returned no image.');

        $bytes = $output->getContent();
        $mimeType = getimagesizefromstring($bytes)['mime'] ?? throw new \RuntimeException('The image model returned an unreadable image.');
        $extension = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mimeType] ?? throw new \RuntimeException('Unexpected image type '.$mimeType.'.');
        $sha256 = hash('sha256', $bytes);
        $storagePath = 'cleaned/'.substr($sha256, 0, 2).'/'.$sha256.'.'.$extension;
        (new Filesystem())->dumpFile($this->directory.'/'.$storagePath, $bytes);
        $sourceUrl = $this->metadata->upload($this->directory.'/'.$storagePath, 'scanfony/'.$storagePath, $mimeType);
        [$width, $height] = getimagesizefromstring($bytes);

        $image->imageMetadata[self::KEY] = [
            'inputHash' => $inputHash, 'storagePath' => $storagePath, 'sourceUrl' => $sourceUrl, 'sha256' => $sha256,
            'mimeType' => $mimeType, 'width' => $width, 'height' => $height, 'provider' => 'gemini', 'model' => $this->model,
            'prompt' => self::PROMPT, 'jev' => $run['answers'][Q::CLEANUP], 'durationMs' => (int) round((microtime(true) - $started) * 1000),
            'evidence' => false, 'createdAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];
    }
}
