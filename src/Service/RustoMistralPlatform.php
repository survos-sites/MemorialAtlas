<?php
declare(strict_types=1);
namespace App\Service;

use Symfony\AI\Platform\Bridge\Mistral\{Factory, Mistral, Ocr};
use Symfony\AI\Platform\{Capability, PlatformInterface};
use Symfony\AI\Platform\ModelCatalog\InMemoryModelCatalog;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class RustoMistralPlatform
{
    public function __construct(private HttpClientInterface $http,
        #[Autowire('%env(MISTRAL_RUSTO_BASE_URL)%')] private string $baseUrl,
        #[Autowire('%env(MISTRAL_RUSTO_API_KEY)%')] private string $apiKey,
        #[Autowire('%env(MISTRAL_RUSTO_CHAT_MODEL)%')] private string $chatModel,
        private ?\Symfony\Contracts\EventDispatcher\EventDispatcherInterface $eventDispatcher = null,
    ) {}

    public function create(): PlatformInterface
    {
        $ocr=['class'=>Ocr::class,'capabilities'=>[Capability::INPUT_IMAGE,Capability::INPUT_PDF,Capability::OUTPUT_TEXT]];
        $models=['small'=>$ocr,'tiny'=>$ocr];
        // The current service is OCR-only. Advertise chat only when explicitly configured.
        if ($this->chatModel!=='') {
            $models[$this->chatModel]=['class'=>Mistral::class,'capabilities'=>[Capability::INPUT_MESSAGES,Capability::INPUT_IMAGE,Capability::OUTPUT_TEXT,Capability::OUTPUT_STRUCTURED]];
        }
        return Factory::createPlatform($this->apiKey, $this->http, new InMemoryModelCatalog($models), eventDispatcher:$this->eventDispatcher,name:'mistral_rusto',baseUrl:$this->baseUrl);
    }
}
