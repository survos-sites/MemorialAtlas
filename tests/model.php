<?php

declare(strict_types=1);

use App\Entity\{Item, Media, Image, Audio};
use App\Workflow\{ItemWorkflow, MediaWorkflow};
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$kernel = new App\Kernel('test', true);
$kernel->boot();
$container = $kernel->getContainer()->get('test.service_container');
$items = $container->get(ItemWorkflow::class);

$check = static function (bool $condition, string $message): void {
    if (!$condition) { throw new RuntimeException($message); }
};
$item = new Item('Coat with a spoken note');
$check(!$items->can($item, 'submit'), 'An empty item must not be submitted.');
$image = new Image($item, 0, 'coat.jpg', 'aa/image', 'image/jpeg', str_repeat('a', 64));
$audio = new Audio($item, 1, 'note.webm', 'bb/audio', 'audio/webm', str_repeat('b', 64));
$check($item->media->count() === 2 && $item->media->last() === $audio, 'Capture order must be preserved.');
$check($audio->transcript === null, 'Audio does not require a transcript at import time.');
$items->apply($item, 'submit');
$items->apply($item, 'process');
$items->apply($item, 'fail');
$items->apply($item, 'retry');
$check($item->marking === 'ready', 'Failed items must be retryable.');
$items->apply($item, 'process');
$items->apply($item, 'propose');
$items->apply($item, 'approve');
$check($item->marking === 'complete', 'Approved items must be complete.');
$mediaWorkflow = $container->get('workflow.registry')->get($image, 'media');
$mediaWorkflow->apply($image, 'process');
$check(!$mediaWorkflow->can($image, 'finish'), 'Image requires assessment before finish.');
$image->aiResults[App\Decision\ImageQuestions::RESULT] = ['route'=>'text'];
$image->marking = 'assessed';
$mediaWorkflow->apply($image, 'finish');
$check($image->marking === 'processed', 'Media processing must persist its marking.');
$check(!$items->can($item, 'submit'), 'Complete items must not be submitted again.');
$kernel->shutdown();
echo "Entity and attribute workflow checks passed.\n";

$check(abs(App\Service\PhotoMetadata::coordinate(['38/1','42/1','30/1'], 'N', 90) - 38.7083333333) < 0.000001, 'EXIF latitude conversion.');
$check(App\Service\PhotoMetadata::coordinate(['78/1','0/1','0/1'], 'W', 180) === -78.0, 'Western longitude sign.');
$check(App\Service\PhotoMetadata::coordinate([], '', 90) === null, 'Missing GPS must stay null.');
$check(App\Service\PhotoMetadata::coordinate(['1/0','0/1','0/1'], 'N', 90) === null, 'Invalid GPS fractions must stay null.');

$kernel = new App\Kernel('test', true);
$kernel->boot();
$services = $kernel->getContainer()->get('test.service_container');
$liveManager = $services->get('doctrine')->getManager();
$em = new Doctrine\ORM\EntityManager(
    Doctrine\DBAL\DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]),
    $liveManager->getConfiguration(),
);
(new Doctrine\ORM\Tools\SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
$item = new Item('Mixed-media persistence check');
$image = new Image($item, 0, 'photo.jpg', 'aa/image', 'image/jpeg', str_repeat('a',64));
$image->description = 'A blue coat.';
$audio = new Audio($item, 1, 'note.webm', 'bb/audio', 'audio/webm', str_repeat('b',64));
$audio->transcript = 'This belonged to my grandmother.';
$cemetery = new App\Entity\Cemetery();
$cemetery->name = 'Test Cemetery';
$cemetery->locality = 'Sperryville';
$em->persist($cemetery);
$item->cemetery = $cemetery;
$em->persist($item);
$em->flush();
$id = $item->id;
$em->clear();
$item = $em->find(Item::class, $id);
$check($item->cemetery->name === 'Test Cemetery' && $item->cemetery->locality === 'Sperryville', 'Cemetery context must round-trip.');
$check($item->media->first() instanceof Image, 'An image must hydrate through the base association as Image.');
$check($item->media->last() instanceof Audio, 'Audio must hydrate through the base association as Audio.');
$check($item->media->last()->transcript === 'This belonged to my grandmother.', 'The audio transcript must round-trip.');
$check($em->getRepository(Image::class)->count() === 1 && $em->getRepository(Audio::class)->count() === 1, 'Subclass listings must be isolated.');
$check(!property_exists($item->media->last(), 'ocrText'), 'Audio must not expose image-only fields.');
$twig = $services->get('twig');
foreach (['card','show'] as $view) {
    $imageHtml = $twig->render('media/_image_'.$view.'.html.twig', ['media' => $item->media->first()]);
    $audioHtml = $twig->render('media/_audio_'.$view.'.html.twig', ['media' => $item->media->last()]);
    $check(str_contains($imageHtml, '<img') && !str_contains($imageHtml, '<audio'), 'Images must use an image view.');
    $check(str_contains($audioHtml, '<audio') && str_contains($audioHtml, 'grandmother') && !str_contains($audioHtml, '<img'), 'Audio must show its player and transcript.');
}
$em->close();
$kernel->shutdown();
echo "Single-table inheritance, ordered hydration and media views passed.\n";
