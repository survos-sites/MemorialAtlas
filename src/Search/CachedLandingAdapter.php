<?php

declare(strict_types=1);

namespace App\Search;

use Survos\SearchBundle\Adapter\{AdapterInterface, HybridSearchAdapterInterface, SemanticSearchAdapterInterface};
use Survos\SearchBundle\Search\{Query, SearchInterface};
use Survos\SearchBundle\Search\ResultSet\ResultSet;
use Survos\SearchBundle\Service\ElasticIndexNameResolver;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Cache\{CacheInterface, ItemInterface};

#[Exclude]
final readonly class CachedLandingAdapter implements AdapterInterface, SemanticSearchAdapterInterface, HybridSearchAdapterInterface
{
    public function __construct(private AdapterInterface $inner, private CacheInterface $cache,
        private ElasticIndexNameResolver $indexNames, private string $cluster, private string $locale) {}

    public function search(Query $query, SearchInterface $search): ResultSet
    {
        if (!$search instanceof ItemSearch || $query->hydrateEntities || $query->getQueryString() !== ''
            || $query->getCurrentPage() !== 1 || $query->getActiveFilters() !== []
            || $query->getActiveSort() !== null || $query->getActiveHitsPerPage() !== 12
            || $query->getSearchMode() !== 'lexical') {
            return $this->inner->search($query, $search);
        }

        $parameters = $search->getResolvedAdapterParameters();
        foreach (['documentProvider', 'documentMapper', 'embeddingText', 'embeddingProvider', 'queryVector'] as $key) {
            unset($parameters[$key]);
        }
        $key = 'landing.items.v2.'.hash('sha256', serialize([
            $this->cluster, $this->indexNames->uid($search), $this->locale, $parameters, $query,
        ]));

        return $this->cache->get($key, function (ItemInterface $item) use ($query, $search): ResultSet {
            // Indexing and review changes become visible within one minute.
            $item->expiresAfter(60);
            return $this->inner->search($query, $search);
        });
    }

    public function configureParameters(OptionsResolver $resolver): void
    {
        $this->inner->configureParameters($resolver);
    }

    public function supportsSemanticSearch(SearchInterface $search): bool
    {
        return $this->inner instanceof SemanticSearchAdapterInterface && $this->inner->supportsSemanticSearch($search);
    }

    public function supportsHybridSearch(SearchInterface $search): bool
    {
        return $this->inner instanceof HybridSearchAdapterInterface && $this->inner->supportsHybridSearch($search);
    }
}
