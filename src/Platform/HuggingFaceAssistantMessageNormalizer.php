<?php

declare(strict_types=1);

namespace App\Platform;

use Symfony\AI\Platform\Contract\Normalizer\Message\AssistantMessageNormalizer;
use Symfony\AI\Platform\Message\AssistantMessage;
use Symfony\Component\Serializer\Normalizer\{NormalizerAwareInterface, NormalizerInterface};

/**
 * Sends an assistant message to the Hugging Face router without its reasoning. The shared normalizer
 * adds `reasoning_content`, which the router rejects with a 400 ("property
 * 'messages.2.assistant.reasoning_content' is unsupported"), so every second chat turn with a thinking
 * model such as Qwen3.8 failed. The Mistral bridge has its own normalizer for the same problem; the
 * Hugging Face bridge does not yet. Remove this class when it does.
 */
final class HuggingFaceAssistantMessageNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    private AssistantMessageNormalizer $inner;

    public function __construct()
    {
        $this->inner = new AssistantMessageNormalizer();
    }

    public function setNormalizer(NormalizerInterface $normalizer): void
    {
        $this->inner->setNormalizer($normalizer);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AssistantMessage;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [AssistantMessage::class => true];
    }

    /** @return array<string, mixed> */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $array = $this->inner->normalize($data, $format, $context);
        unset($array['reasoning_content']);

        return $array;
    }
}
