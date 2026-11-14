<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class SetupStatus
{
    public function __construct(
        #[Autowire('%env(S3_ENDPOINT)%')] private string $s3Endpoint,
        #[Autowire('%env(AWS_S3_BUCKET_NAME)%')] private string $bucket,
        #[Autowire('%env(AWS_S3_ACCESS_ID)%')] private string $s3Key,
        #[Autowire('%env(AWS_S3_ACCESS_SECRET)%')] private string $s3Secret,
        #[Autowire('%env(IMAGOR_HOST)%')] private string $imagorHost,
        #[Autowire('%env(IMAGOR_SECRET)%')] private string $imagorSecret,
        #[Autowire('%env(MISTRAL_API_KEY)%')] private string $mistralKey,
        #[Autowire('%env(OPENAI_API_KEY)%')] private string $openaiKey,
        #[Autowire('%env(default::HF_TOKEN)%')] private ?string $hfToken,
        #[Autowire('%env(TYPESAFE_API_KEY)%')] private string $jevKey,
        #[Autowire('%env(SCANFONY_VISION_PLATFORM)%')] private string $visionPlatform,
        #[Autowire('%env(SCANFONY_VISION_MODEL)%')] private string $visionModel,
        #[Autowire('%env(SCANFONY_JEV_MODEL)%')] private string $decisionModel,
        #[Autowire('%env(SCANFONY_OCR_MODEL)%')] private string $ocrModel,
        #[Autowire('%env(bool:SCANFONY_OCR_ENABLED)%')] private bool $ocrEnabled,
        #[Autowire('%env(SCANFONY_OCR_PLATFORM)%')] private string $ocrPlatform,
        #[Autowire('%env(SCANFONY_EXTRACTION_PLATFORM)%')] private string $extractionPlatform,
        #[Autowire('%env(SCANFONY_EXTRACTION_MODEL)%')] private string $extractionModel,
        #[Autowire('%env(bool:SCANFONY_VISION_ENABLED)%')] private bool $visionEnabled,
    ) {}

    public function requirements(): array
    {
        return [
            ['name' => 'PHP 8.5+ with PostgreSQL and GD', 'configured' => PHP_VERSION_ID >= 80500 && extension_loaded('pdo_pgsql') && extension_loaded('gd'), 'required' => true],
            ['name' => 'S3 endpoint, bucket and credentials', 'configured' => $this->present($this->s3Endpoint, $this->bucket, $this->s3Key, $this->s3Secret), 'required' => true],
            ['name' => 'Imagor endpoint and signing secret', 'configured' => $this->present($this->imagorHost, $this->imagorSecret), 'required' => true],
            ['name' => 'TypeSafe API key and Jev model', 'configured' => $this->present($this->jevKey, $this->decisionModel), 'required' => true],
            ['name' => 'Mistral API key', 'configured' => $this->present($this->mistralKey), 'required' => ($this->visionEnabled && $this->visionPlatform === 'mistral') || $this->extractionPlatform === 'mistral' || ($this->ocrEnabled && $this->ocrPlatform === 'mistral')],
            ['name' => 'OpenAI API key', 'configured' => $this->present($this->openaiKey), 'required' => ($this->visionEnabled && $this->visionPlatform === 'openai') || $this->extractionPlatform === 'openai'],
            ['name' => 'Hugging Face token (integration planned)', 'configured' => $this->present($this->hfToken), 'required' => false],
        ];
    }

    public function models(): array
    {
        return [
            ['stage' => 'Image metadata', 'provider' => 'Imagor', 'model' => '/meta', 'enabled' => true],
            ['stage' => 'OCR / image decisions', 'provider' => 'TypeSafe Jev', 'model' => $this->decisionModel, 'enabled' => true],
            ['stage' => 'Primary OCR', 'provider' => $this->ocrPlatform, 'model' => $this->ocrModel, 'enabled' => $this->ocrEnabled],
            ['stage' => 'Optional visual description', 'provider' => $this->visionPlatform, 'model' => $this->visionModel, 'enabled' => $this->visionEnabled],
            ['stage' => 'Structured catalog extraction (text)', 'provider' => $this->extractionPlatform, 'model' => $this->extractionModel, 'enabled' => true],
        ];
    }

    private function present(?string ...$values): bool
    {
        foreach ($values as $value) if ($value === null || trim($value) === '') return false;
        return true;
    }
}
