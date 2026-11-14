<?php

declare(strict_types=1);

use App\Entity\{Image, Item};
use App\Mcp\MemorialTools;
use App\Service\{Remembrance, SymbolFinder};

require dirname(__DIR__).'/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$check = static function (bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); };

$item = new Item('Renzy gravestone');
$item->aiResults['catalog']['record'] = ['people' => [
    ['name' => 'Bernard Thomas Renzy', 'birth' => 'Jan. 10, 1937', 'death' => 'Dec. 20, 2015', 'inscription' => 'Captain, U.S. Army Reserves'],
    ['name' => 'Sandra Welborn', 'birth' => 'Aug. 26, 1940', 'death' => null, 'inscription' => null],
]];
$image = new Image($item, 0, 'renzy.jpg', 'aa/renzy', 'image/jpeg', hash('sha256', 'renzy'));
$image->ocrText = "RENZY\nBERNARD THOMAS\nCaptain, U.S. Army Reserves\nTOGETHER FOREVER";
$image->aiResults[SymbolFinder::RESULT] = ['items' => [['name' => 'scales of justice', 'category' => 'work', 'meaning' => 'lawyer or judge'],
    ['name' => 'U.S. Army emblem', 'category' => 'military', 'meaning' => 'served in the U.S. Army']],
    'scripture' => [['text' => 'The Lord is my shepherd', 'citation' => 'Psalm 23:1', 'book' => 'Psalms']]];

$evidence = Remembrance::evidence($item);
$check(count($evidence['people']) === 2 && $evidence['people'][0]['inscription'] === 'Captain, U.S. Army Reserves', 'The evidence keeps each person with what is carved for them.');
$check(array_column($evidence['symbols'], 'meaning') === ['lawyer or judge', 'served in the U.S. Army'], 'The evidence includes the symbols and their common meaning.');
$check($evidence['scripture'][0]['citation'] === 'Psalm 23:1' && str_contains($evidence['inscriptionText'], 'TOGETHER FOREVER'), 'The evidence includes scripture and the carved text.');

$empty = new Item('Unread stone');
$check(Remembrance::evidence($empty) === null, 'A record without people gets no line, and no model call.');

$kernel = new App\Kernel('test', true);
$kernel->boot();
$services = $kernel->getContainer()->get('test.service_container');
$em = $services->get('doctrine')->getManager();
$item->marking = 'review';
$item->aiResults[Remembrance::RESULT] = ['line' => 'Bernard Thomas Renzy was a soldier and, it seems, a lawyer.', 'basis' => ['Captain, U.S. Army Reserves', 'scales of justice']];
$em->persist($item);
$em->flush();
try {
    $record = $services->get(MemorialTools::class)->get($item->id);
    $check($record['rememberedAs']['line'] === $item->aiResults[Remembrance::RESULT]['line'] && $record['rememberedAs']['basis'] !== [], 'The record returns the line with its evidence.');
} finally {
    $em->remove($em->find(Item::class, $item->id));
    $em->flush();
}

echo "remembrance: ok\n";
