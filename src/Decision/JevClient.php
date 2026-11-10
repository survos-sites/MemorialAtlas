<?php

declare(strict_types=1);

namespace App\Decision;

use Symfony\AI\Platform\Bridge\Decision\Evaluation;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/** Uses the current System One assets API; the installed TypeSafe bridge is text-only. */
final readonly class JevClient
{
    public function __construct(
        private HttpClientInterface $http,
        #[Autowire('%env(TYPESAFE_API_KEY)%')] private string $key,
        #[Autowire('%env(SCANFONY_JEV_MODEL)%')] public string $model,
    ) {}

    public function evaluate(array $state, array $questions, ?string $jpeg = null): array
    {
        if (trim($this->key) === '') throw new \RuntimeException('Configure TYPESAFE_API_KEY before running image decisions.');
        $body = ['model' => $this->model] + (new Evaluation($state, $questions))->jsonSerialize();
        $headers = [];
        if ($jpeg !== null) {
            // https://docs.typesafe.ai/images: opaque asset reference and explicit experimental opt-in.
            $ref = 'scanfony-photo-'.hash('sha256', $jpeg);
            $body['state']['photo'] = $ref;
            $body['assets'] = [$ref => ['type'=>'image', 'format'=>'jpg', 'data_base64'=>base64_encode($jpeg)]];
            $headers['TypeSafe-Danger-Zone-Images'] = 'true';
        }
        $started = microtime(true);
        $httpResponse = $this->http->request('POST', 'https://api.typesafe.ai/v1/systemone', [
            'auth_bearer'=>$this->key, 'headers'=>$headers, 'json'=>$body, 'max_duration'=>60,
        ]);
        if ($httpResponse->getStatusCode() >= 400) {
            throw new \RuntimeException('Jev HTTP '.$httpResponse->getStatusCode().': '.mb_substr($httpResponse->getContent(false), 0, 1200));
        }
        $response = $httpResponse->toArray();
        if (!is_string($response['model'] ?? null) || !is_int($response['usage']['input_tokens'] ?? null)
            || !is_int($response['usage']['output_tokens'] ?? null)) throw new \UnexpectedValueException('Jev returned incomplete model/usage metadata.');
        foreach ($questions as $id => $question) {
            $a = $response['answers'][$id] ?? null;
            $options = $question->jsonSerialize()['criteria'];
            if (!is_array($a) || !is_string($a['choice'] ?? null) || !array_key_exists($a['choice'], $options)
                || !is_numeric($a['confidence'] ?? null) || $a['confidence'] < 0 || $a['confidence'] > 1
                || !is_array($a['probabilities'] ?? null) || array_diff_key($options, $a['probabilities']) !== []
                || array_diff_key($a['probabilities'], $options) !== []) throw new \UnexpectedValueException('Invalid Jev answer: '.$id);
            foreach ($a['probabilities'] as $p) {
                if (!is_numeric($p) || !is_finite((float)$p) || $p < 0 || $p > 1) throw new \UnexpectedValueException('Invalid probability: '.$id);
            }
            if (abs(array_sum($a['probabilities']) - 1) > 0.025) throw new \UnexpectedValueException('Invalid distribution: '.$id);
        }
        unset($body['assets']);
        return ['request'=>$body, 'model'=>$response['model'], 'answers'=>$response['answers'], 'usage'=>$response['usage'],
            'durationMs'=>(int)round((microtime(true)-$started)*1000), 'imageSha256'=>$jpeg === null ? null : hash('sha256',$jpeg),
            'createdAt'=>(new \DateTimeImmutable())->format(DATE_ATOM)];
    }
}
