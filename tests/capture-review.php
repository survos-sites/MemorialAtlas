<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$kernel = new App\Kernel('test', true); $kernel->boot();
$container = $kernel->getContainer()->get('test.service_container');
$live = $container->get('doctrine')->getManager();
$em = new Doctrine\ORM\EntityManager(Doctrine\DBAL\DriverManager::getConnection(['driver'=>'pdo_sqlite','memory'=>true]), $live->getConfiguration());
(new Doctrine\ORM\Tools\SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
$cemetery = new App\Entity\Cemetery(); $cemetery->name = 'Test'; $em->persist($cemetery);
$images = [];
for ($n = 1; $n <= 2; ++$n) {
    $item = new App\Entity\Item('Test '.$n); $item->cemetery = $cemetery;
    $image = new App\Entity\Image($item, 0, '20261110_00000'.$n.'.jpg', 'test/'.$n, 'image/jpeg', hash('sha256', (string)$n));
    $image->marking = 'processed'; $image->ocrText = 'MARY JONES 1900–1980';
    $em->persist($item); $images[] = $image;
}
$em->flush(); $calls = 0;
$http = new Symfony\Component\HttpClient\MockHttpClient(static function ($method, $url, $options) use (&$calls) {
    ++$calls;
    $body = json_decode($options['body'], true, 512, JSON_THROW_ON_ERROR);
    if (isset($body['state']['previous']['itemId']) || !isset($body['state']['current']['ocr'])) throw new RuntimeException('Send evidence, not the existing grouping label.');
    return new Symfony\Component\HttpClient\Response\JsonMockResponse([
        'model'=>'test-jev', 'usage'=>['input_tokens'=>100,'output_tokens'=>20],
        'answers'=>['relationship'=>['choice'=>'same_marker','confidence'=>0.99,
            'probabilities'=>['same_marker'=>0.99,'different_marker'=>0.0,'associated_detail'=>0.0,'uncertain'=>0.01]]],
    ]);
});
$tester = new Symfony\Component\Console\Tester\CommandTester(new App\Command\ReviewCaptureCommand($em, new App\Decision\JevClient($http, 'test-key', 'test-jev')));
$tester->execute(['cemetery'=>$cemetery->id]);
if ($tester->getStatusCode() !== 0 || $calls !== 1) throw new RuntimeException('Expected one successful comparison.');
if ($images[0]->item === $images[1]->item || $em->getRepository(App\Entity\Item::class)->count() !== 2) throw new RuntimeException('A suggestion must never merge items.');
if ($images[1]->aiResults['captureReview']['previousMediaId'] !== $images[0]->id) throw new RuntimeException('Missing source evidence link.');
$tester->execute(['cemetery'=>$cemetery->id]);
if ($calls !== 1) throw new RuntimeException('Identical evidence should reuse its decision.');
$images[1]->ocrText = 'Revised reading'; $em->flush();
$tester->execute(['cemetery'=>$cemetery->id]);
if ($calls !== 2) throw new RuntimeException('Changed evidence must invalidate the cached decision.');
$kernel->shutdown();
echo "Capture review: evidence links, no automatic merging, cache reuse and invalidation passed.\n";
