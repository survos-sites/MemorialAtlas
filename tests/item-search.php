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
    $detail = new App\Entity\Image($item,1,'detail.jpg','test/detail','image/jpeg',hash('sha256','detail'.$n));
    $detail->ocrText = $n === 0 ? 'Unique closeup inscription' : 'Other inscription';
    $detail->description = $n === 0 ? 'A carved sailing ship' : 'A cross';
    $audio = new App\Entity\Audio($item,2,'note.wav','test/audio','audio/wav',hash('sha256','audio'.$n));
    $audio->transcript = $n === 0 ? 'Restored by the community' : 'No note';
    $em->persist($item); $em->flush();
    if ($n === 0) $item->aiResults['markerFacts']['record'] = ['orientation'=>'upright','form'=>'headstone','people'=>[
        ['name'=>'Eleanor Whitfield','familyName'=>'WHITFIELD','birth'=>'1900','death'=>'1980','commemorated'=>true],
        ['name'=>'Partner','familyName'=>'Whitfield','birth'=>'1901','death'=>null,'commemorated'=>true],
        ['name'=>'Parent mentioned','familyName'=>'Jones','birth'=>null,'death'=>'1850','commemorated'=>false],
    ],'symbols'=>['cross'],'themes'=>['remembrance'],'epitaph'=>'Always remembered'];
    $docs[] = get_object_vars(App\Search\ItemDocument::fromItem($item));
}
$check($docs[0]['familyName'] === ['Whitfield','Jones'], 'Family facets must deduplicate capitalization and retain multiple surnames.');
$check($docs[0]['namedPeopleCount'] === 3 && $docs[0]['recordedDeathCount'] === 1 && $docs[0]['birthOnlyCount'] === 1 && $docs[0]['otherNamedPeopleCount'] === 1, 'Count commemorated dates separately from mentioned relatives.');
$check($docs[1]['namedPeopleCount'] === null && $docs[1]['markerOrientation'] === 'Not assessed', 'Unassessed facts must not become zero or absent.');
$check($docs[0]['imageCount'] === 2, 'Count images, not audio, in the photo count.');
$check(!isset($docs[0]['aiResults'], $docs[0]['storagePath']), 'Public documents must omit raw processing evidence and local paths.');
[$descriptor,$client,$parameters,$search] = $c->get(Survos\ElasticBundle\Service\ElasticIndexService::class)->forEntityClass(App\Entity\Item::class);
$adapter = $c->get(Survos\SearchBundle\Adapter\AdapterProvider::class)->getAdapter('es');
$parameters['index'] = 'test-items-'.bin2hex(random_bytes(6));
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
    $check($all->getFacetDistribution('markerOrientation')->getValues()['upright'] === 1, 'Marker orientation must be a searchable facet.');
    $familyQuery = $search->createQuery()->addActiveFilter(new Survos\SearchBundle\Search\Filter\TermFilter('familyName',['Whitfield']));
    $check($adapter->search($familyQuery,$search)->getTotalResults() === 1, 'Family facet must filter aggregate items.');
    $query->setQueryString('eleanor');
    $check($adapter->search($query,$search)->getTotalResults() === 1, 'Extracted names must be searchable even when absent from title/OCR.');
    foreach (['closeup', 'sailing', 'restored'] as $term) {
        $query->setQueryString($term);
        $check($adapter->search($query,$search)->getTotalResults() === 1, 'Secondary photo OCR, observations and audio must each find one aggregate item.');
    }
    $query->setQueryString('')->addActiveFilter(new Survos\SearchBundle\Search\Filter\TermFilter('source',['Local']));
    $filtered = $adapter->search($query,$search);
    $check($filtered->getTotalResults() === 2, 'Source must filter before pagination.');
    $check($filtered->getFacetDistribution('source')->getValues()['TombReader'] === 1, 'Facet must retain its alternate selections.');
} finally {
    $client->deleteIndex($index);
    $kernel->shutdown();
}
echo "Item search: one hit per record, all photo evidence, audio, extracted names, facets and pagination passed.\n";
