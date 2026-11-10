<?php
declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$kernel = new App\Kernel('test', true);
$kernel->boot();
$c = $kernel->getContainer()->get('test.service_container');
$check = static function (bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); };
$live = $c->get('doctrine')->getManager();
$em = new Doctrine\ORM\EntityManager(Doctrine\DBAL\DriverManager::getConnection(['driver'=>'pdo_sqlite','memory'=>true]), $live->getConfiguration());
(new Doctrine\ORM\Tools\SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
$docs = [];
foreach (['Local','TombReader','Local'] as $n => $source) {
    $item = new App\Entity\Item('Memorial');
    $item->aiResults['catalog']['record']['people'] = [['name'=>$n === 0 ? 'Eleanor Whitfield' : 'Thomas Baker']];
    $image = new App\Entity\Image($item,0,'test.jpg','test/photo','image/jpeg',hash('sha256',(string)$n));
    $image->source = $source;
    $image->aiResults[App\Decision\ImageQuestions::RESULT]['answers'][App\Decision\ImageQuestions::MEMORIAL] = ['choice'=>$n === 1 ? 'multiple' : 'one'];
    if ($n === 2) $image->aiResults[App\Decision\ImageQuestions::RESULT]['answers'][App\Decision\ImageQuestions::FLOWERS] = ['choice'=>'absent'];
    $em->persist($item); $em->flush();
    $docs[] = get_object_vars(App\Search\ImageDocument::fromImage($image));
}
$check($docs[0]['flowers'] === 'Not assessed' && $docs[2]['flowers'] === 'Absent', 'Missing visual evidence must not imply absence.');
$check(!isset($docs[0]['aiResults'], $docs[0]['storagePath']), 'Public documents must omit raw processing evidence and local paths.');
[$descriptor,$client,$parameters,$search] = $c->get(Survos\ElasticBundle\Service\ElasticIndexService::class)->forEntityClass(App\Entity\Image::class);
$adapter = $c->get(Survos\SearchBundle\Adapter\AdapterProvider::class)->getAdapter('es');
$parameters['index'] = 'test-images-'.bin2hex(random_bytes(6));
$search->setResolvedAdapterParameters($parameters);
$index = $c->get(Survos\SearchBundle\Service\ElasticIndexNameResolver::class)->uid($search);
$client->createIndex($index, ['dynamic'=>'strict','properties'=>$parameters['mappings']]);
try {
    $body = [];
    foreach ($docs as $doc) { $body[] = ['index'=>['_index'=>$index,'_id'=>(string)$doc['id']]]; $body[]=$doc; }
    $bulk = $client->bulk($body);
    $check(!$bulk['errors'], 'Strict field-derived mappings must accept documents.');
    $client->refresh($index);
    $query = $search->createQuery()->setActiveHitsPerPage(1);
    $all = $adapter->search($query,$search);
    $check($all->getTotalResults() === 3 && count($all->getHits()) === 1, 'Pagination must retain full totals.');
    $check($all->getFacetDistribution('source')->getValues()['Local'] === 2, 'Facet counts must span all pages.');
    $query->setQueryString('eleanor');
    $check($adapter->search($query,$search)->getTotalResults() === 1, 'Extracted names must be searchable even when absent from title/OCR.');
    $query->setQueryString('')->addActiveFilter(new Survos\SearchBundle\Search\Filter\TermFilter('source',['Local']));
    $filtered = $adapter->search($query,$search);
    $check($filtered->getTotalResults() === 2, 'Source must filter before pagination.');
    $check($filtered->getFacetDistribution('source')->getValues()['TombReader'] === 1, 'Facet must retain its alternate selections.');
    $query->addActiveFilter(new Survos\SearchBundle\Search\Filter\TermFilter('flowers',['Absent']));
    $check($adapter->search($query,$search)->getTotalResults() === 1, 'Independent facets must intersect.');
    $client->bulk([['delete'=>['_index'=>$index,'_id'=>(string)$docs[2]['id']]]]);
    $client->refresh($index);
    $check($adapter->search($query,$search)->getTotalResults() === 0, 'Deleted documents must disappear.');
} finally {
    $client->deleteIndex($index);
    $kernel->shutdown();
}
echo "Elasticsearch: field-derived mappings, extracted names, source facets, missing evidence, pagination and deletion passed.\n";
