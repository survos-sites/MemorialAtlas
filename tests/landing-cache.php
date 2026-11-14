<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';

use App\Search\{CachedLandingAdapter, ItemSearch};
use Survos\SearchBundle\Adapter\AdapterInterface;
use Survos\SearchBundle\Search\{Query, SearchInterface};
use Survos\SearchBundle\Search\ResultSet\{ResultSet, Hit, FacetTermDistribution};
use Survos\SearchBundle\Search\Filter\TermFilter;
use Survos\SearchBundle\Service\ElasticIndexNameResolver;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\OptionsResolver\OptionsResolver;

$check = static function (bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); };
$inner = new class implements AdapterInterface {
    public int $calls = 0;
    public function configureParameters(OptionsResolver $resolver): void {}
    public function search(Query $query, SearchInterface $search): ResultSet {
        ++$this->calls;
        return (new ResultSet())->setTotalResults(191)->setHits([new Hit(['id'=>62],1.0)])
            ->setFacetDistributions([(new FacetTermDistribution())->setProperty('source')->setValues(['Local'=>191])]);
    }
};
$cache = new ArrayAdapter();
$names = new ElasticIndexNameResolver('test-');
$adapter = new CachedLandingAdapter($inner, $cache, $names, 'cluster-a', 'en');
// Query configuration alone is needed; indexing dependencies are deliberately absent.
$search = (new ReflectionClass(ItemSearch::class))->newInstanceWithoutConstructor();
$search->setResolvedAdapterParameters(['index'=>'items', 'baseFilters'=>[]]);
$query = new Query(); $query->hydrateEntities = false;
$first = $adapter->search($query,$search);
$cached = $adapter->search($query,$search);
$check($inner->calls === 1, 'Repeated default landing query must use cached results.');
$check($cached->getTotalResults() === 191 && $cached->getHits()[0]->getData()['id'] === 62
    && $cached->getFacetDistribution('source')->getValues() === ['Local'=>191], 'Cache must retain hits, totals and facets together.');
foreach ([
    fn($q)=>$q->setQueryString('Wharton'), fn($q)=>$q->setCurrentPage(2),
    fn($q)=>$q->setActiveSort('title'), fn($q)=>$q->setActiveHitsPerPage(24),
    fn($q)=>$q->addActiveFilter(new TermFilter('source',['Local'])),
    fn($q)=>$q->setSearchMode('semantic'), fn($q)=>$q->hydrateEntities=true,
] as $change) {
    $other=clone $query; $change($other); $before=$inner->calls;
    $adapter->search($other,$search); $adapter->search($other,$search);
    $check($inner->calls === $before+2, 'Queries outside the default landing state must bypass the cache.');
}
foreach ([['cluster-b','en'],['cluster-a','fr']] as [$cluster,$locale]) {
    $before=$inner->calls;
    (new CachedLandingAdapter($inner,$cache,$names,$cluster,$locale))->search($query,$search);
    $check($inner->calls === $before+1, 'Cluster and locale must have separate cache entries.');
}
$before=$inner->calls;
$search->setResolvedAdapterParameters(['index'=>'items', 'baseFilters'=>[['term'=>['cemetery'=>'Other']]]]);
$adapter->search($query,$search);
$check($inner->calls === $before+1, 'Different search scopes must have separate entries.');
echo "Landing cache: hits, totals, facets, bypass states and scope isolation passed.\n";
