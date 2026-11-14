<?php

declare(strict_types=1);

use App\Decision\ImageQuestions as Q;
use App\Entity\{Image, Item};
use App\Image\ImagePreset;
use App\Service\{ImageCleaner, ImageDelivery};

require dirname(__DIR__).'/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$kernel = new App\Kernel('test', true);
$kernel->boot();
$services = $kernel->getContainer()->get('test.service_container');
$check = static function (bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); };
$answer = static fn (string $choice, float $confidence = 0.9, float $p = 0.95) => ['choice' => $choice, 'confidence' => $confidence, 'probabilities' => [$choice => $p]];

$image = new Image(new Item('Cleaner test'), 0, 'stone.jpg', 'aa/stone', 'image/jpeg', str_repeat('a', 64));
$image->sourceUrl = 'https://example.org/stone.jpg';

$image->aiResults[Q::RESULT]['answers'] = [Q::TYPE => $answer('gravestone'), Q::ROLE => $answer('subject')];
$check(ImageCleaner::eligible($image), 'The main photo of a gravestone is eligible.');
$image->aiResults[Q::RESULT]['answers'][Q::TYPE] = $answer('place_reference');
$check(!ImageCleaner::eligible($image), 'A sign or place reference is not cleaned.');
$image->aiResults[Q::RESULT]['answers'] = [Q::TYPE => $answer('gravestone'), Q::ROLE => $answer('context')];
$check(!ImageCleaner::eligible($image), 'A context photo is not cleaned.');

$check(ImageCleaner::wanted(['answers' => [Q::CLEANUP => $answer('angled', 0.7, 0.84)]]), 'An angled photo gets a cleaned view.');
$check(!ImageCleaner::wanted(['answers' => [Q::CLEANUP => $answer('angled', 0.3, 0.53)]]), 'A barely angled photo must not trigger the image model.');
$check(!ImageCleaner::wanted(['answers' => [Q::CLEANUP => $answer('square')]]), 'A straight-on photo is left alone.');
$check(!ImageCleaner::wanted(['answers' => []]), 'No Jev answer means no cleaned view.');
$check(str_contains(ImageCleaner::PROMPT, 'Do not change') && str_contains(ImageCleaner::PROMPT, 'punctuation'), 'The prompt forbids changing characters.');

$delivery = $services->get(ImageDelivery::class);
$check($delivery->cleaned($image, ImagePreset::Thumb) === null, 'No cleaned view without metadata.');
$storagePath = 'cleaned/test/'.bin2hex(random_bytes(8)).'.jpg';
$file = $kernel->getProjectDir().'/var/media/'.$storagePath;
$image->imageMetadata[ImageCleaner::KEY] = ['storagePath' => $storagePath, 'sourceUrl' => 's3://bucket/scanfony/'.$storagePath, 'evidence' => false];
$check($delivery->cleaned($image, ImagePreset::Thumb) === null, 'Dev and test need the local copy of the cleaned view.');
(new Symfony\Component\Filesystem\Filesystem())->dumpFile($file, 'jpeg');
try {
    $url = $delivery->cleaned($image, ImagePreset::Detail);
    $check(is_string($url) && str_contains(rawurldecode($url), $storagePath), 'The cleaned view is served from its own file.');
    $check($url !== $delivery->detail($image), 'The cleaned view never replaces the original URL.');
} finally {
    (new Symfony\Component\Filesystem\Filesystem())->remove(dirname($file));
}
$kernel->shutdown();
echo "Image cleaner: Jev gate, eligibility, labelled storage and delivery passed.\n";
