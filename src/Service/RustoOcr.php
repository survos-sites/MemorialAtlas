<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Client for the local rusto OCR service (~/sites/ocr-api), which speaks Mistral's POST /v1/ocr
 * and picks the model per request. Kept free of scanfony types so the OCR lab can move into a
 * bundle later.
 */
final class RustoOcr
{
    public function __construct(
        private readonly HttpClientInterface $http,
        #[Autowire(env: 'default:rusto_ocr_default:RUSTO_OCR_URL')] private readonly string $baseUrl,
        #[Autowire(env: 'default::RUSTO_OCR_KEY')] private readonly ?string $apiKey = null,
    ) {
    }

    /** @return array<string, string> */
    private function auth(): array
    {
        return $this->apiKey ? ['Authorization' => 'Bearer '.$this->apiKey] : [];
    }

    public function baseUrl(): string
    {
        return rtrim($this->baseUrl, '/');
    }

    /** @return list<string> model names the service offers; throws when it is unreachable */
    public function models(): array
    {
        $data = $this->http->request('GET', $this->baseUrl().'/v1/models', ['headers' => $this->auth(), 'timeout' => 5])->toArray()['data'] ?? [];

        return array_values(array_map(static fn (array $m): string => (string) $m['id'], $data));
    }

    /** @return array{model: string, text: string, ms: int, chars: int, lines: int, error: ?string} */
    public function recognize(string $imageBytes, string $mimeType, string $model): array
    {
        $started = hrtime(true);
        try {
            $page = $this->http->request('POST', $this->baseUrl().'/v1/ocr', [
                'headers' => $this->auth(),
                'json' => [
                    'model' => $model,
                    'document' => ['type' => 'image_url', 'image_url' => sprintf('data:%s;base64,%s', $mimeType, base64_encode($imageBytes))],
                ],
                'timeout' => 120,
            ])->toArray()['pages'][0] ?? [];
            $text = (string) ($page['markdown'] ?? '');

            return ['model' => $model, 'text' => $text, 'ms' => $this->since($started), 'chars' => mb_strlen($text), 'lines' => count($page['lines'] ?? []), 'error' => null];
        } catch (\Throwable $e) {
            return ['model' => $model, 'text' => '', 'ms' => $this->since($started), 'chars' => 0, 'lines' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Same call, but the service fetches the image itself (its OCR_ALLOWED_HOSTS must list the host), so a
     * full-size photo does not travel through this machine's upload link.
     *
     * @return array{model: string, ms: int, page: array<string, mixed>, error: ?string}
     */
    public function recognizeUrl(string $imageUrl, string $model, ?bool $autorotate = null): array
    {
        $started = hrtime(true);
        try {
            $json = ['model' => $model, 'document' => ['type' => 'image_url', 'image_url' => $imageUrl]];
            if ($autorotate !== null) {
                $json['autorotate'] = $autorotate;
            }
            $page = $this->http->request('POST', $this->baseUrl().'/v1/ocr', ['headers' => $this->auth(), 'json' => $json, 'timeout' => 180])->toArray()['pages'][0] ?? [];

            return ['model' => $model, 'ms' => $this->since($started), 'page' => $page, 'error' => null];
        } catch (\Throwable $e) {
            return ['model' => $model, 'ms' => $this->since($started), 'page' => [], 'error' => $e->getMessage()];
        }
    }

    /** Full receipt for a known, already oriented raster. */
    public function recognizePage(string $bytes, string $mimeType, string $model): array
    {
        $data = $this->http->request('POST', $this->baseUrl().'/v1/ocr', [
            'headers' => $this->auth(),
            'json' => ['model' => $model, 'autorotate' => false, 'document' => [
                'type' => 'image_url', 'image_url' => sprintf('data:%s;base64,%s', $mimeType, base64_encode($bytes)),
            ]],
            'timeout' => 180,
        ])->toArray();
        if (!isset($data['pages'][0]['dimensions'], $data['pages'][0]['lines'], $data['pages'][0]['markdown'], $data['pages'][0]['orientation_corrected'])) {
            throw new \RuntimeException('OCR response is missing its page geometry or transcription.');
        }
        return $data['pages'][0];
    }

    private function since(int $started): int
    {
        return (int) round((hrtime(true) - $started) / 1e6);
    }
}
