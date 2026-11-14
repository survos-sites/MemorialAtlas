<?php

declare(strict_types=1);

namespace App\Search;

use Survos\SearchBundle\Adapter\{AdapterFactoryInterface, AdapterInterface};
use Survos\SearchBundle\Adapter\Elasticsearch\ElasticsearchFactory;
use Survos\SearchBundle\Service\ElasticIndexNameResolver;
use Symfony\Component\DependencyInjection\Attribute\{AsDecorator, Autowire, AutowireDecorated};
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\CacheInterface;

#[AsDecorator(decorates: ElasticsearchFactory::class)]
final readonly class CachedSearchFactory implements AdapterFactoryInterface
{
    public function __construct(
        #[AutowireDecorated] private AdapterFactoryInterface $inner,
        #[Autowire(service: 'cache.app')] private CacheInterface $cache,
        private ElasticIndexNameResolver $indexNames,
        private RequestStack $requests,
    ) {}

    public function support(string $dsn): bool
    {
        return $this->inner->support($dsn);
    }

    public function createAdapter(string $dsn): AdapterInterface
    {
        $adapter = $this->inner->createAdapter($dsn);
        $request = $this->requests->getCurrentRequest();
        // Keep indexing and other consumers on the original adapter. Only the public landing route caches results.
        if ($request === null || $request->attributes->get('_route') !== 'item_search') {
            return $adapter;
        }

        return new CachedLandingAdapter($adapter, $this->cache,
            $this->indexNames, hash('sha256', $dsn), $request->getLocale());
    }
}
