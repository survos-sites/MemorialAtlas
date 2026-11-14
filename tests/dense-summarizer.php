<?php

declare(strict_types=1);

use App\Entity\{Image, Item};
use App\Service\{DenseSummarizer, Remembrance};

require dirname(__DIR__).'/vendor/autoload.php';
$check = static function (bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); };

$item = new Item('Gravestone of Edward Frank Geneva');
$item->aiResults['catalog']['record'] = ['people' => [['name' => 'Edward Frank Geneva', 'birth' => 'June 4, 1951', 'death' => 'Jan. 17, 2019', 'inscription' => 'PHYSICIAN, FAMILY, PHYSICIST']]];
$item->aiResults['sameMarker'] = ['sameAs' => [106]];
$image = new Image($item, 0, 'geneva.jpg', 'aa/geneva', 'image/jpeg', hash('sha256', 'geneva'));
$image->ocrText = "EDWARD FRANK GENEVA\nPHYSICIAN, FAMILY, PHYSICIST";

$evidence = DenseSummarizer::evidence($item);
$check($evidence['people'][0]['inscription'] === 'PHYSICIAN, FAMILY, PHYSICIST' && str_contains($evidence['photos'][0]['ocrText'], 'FAMILY'), 'The model gets every reading we have, including raw OCR.');

$codeBuilt = Remembrance::denseSummary($item);
$check(str_contains($codeBuilt, 'FAMILY, PHYSICIST'), 'Without a checked summary, the code-built summary is used.');

$item->aiResults[DenseSummarizer::RESULT] = ['summary' => 'Edward Frank Geneva ("Doc Ed", 1951–2019), physician, father and husband; caduceus and Ohio State logo.',
    'disagreements' => [['field' => 'inscription', 'reading' => 'PHYSICIAN, FAMILY, PHYSICIST', 'photoShows' => 'PHYSICIAN, FATHER, HUSBAND']]];
$checked = Remembrance::denseSummary($item);
$check(str_contains($checked, 'father and husband') && !str_contains($checked, 'FAMILY, PHYSICIST'), 'A photo-checked summary replaces the code-built one.');
$check(str_contains($checked, 'Same stone as #106'), 'Same-stone links are still added by code, since they come from the duplicate step.');
$check(str_contains($checked, 'Readings to check: inscription'), 'Disagreements with the photo are listed, so a person can review them.');

echo "dense-summarizer: ok\n";
