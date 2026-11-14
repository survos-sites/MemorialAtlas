<?php

declare(strict_types=1);

use App\Entity\{Image, Item};
use App\Mcp\MemorialTools;
use App\Service\SymbolFinder;

require dirname(__DIR__).'/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$check = static function (bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); };

$answer = static fn (float $present): array => ['choice' => $present >= 0.5 ? 'present' : 'absent', 'confidence' => 0.9,
    'probabilities' => ['present' => $present, 'absent' => 1 - $present, 'unclear' => 0.0]];
$plain = array_fill_keys(array_keys(SymbolFinder::questions()), $answer(0.05));

$check(SymbolFinder::likely($plain) === [], 'A stone with only text sends nothing to the vision model.');
$check(SymbolFinder::likely(['music' => $answer(0.81)] + $plain) === ['music'], 'A likely instrument sends the photo to the vision model.');
$check(SymbolFinder::likely(['sport' => $answer(0.45)] + $plain) === ['sport'], 'A borderline answer counts: missing a symbol costs more than one vision call.');
$check(SymbolFinder::likely(['sport' => $answer(0.2)] + $plain) === [], 'An unlikely answer does not count.');
$check(SymbolFinder::likely(['scripture' => $answer(0.9)] + $plain) === ['scripture'], 'A likely scripture quote also sends the photo to the vision model.');
$check(App\Bible\BibleBooks::normalize('I Cor.') === '1 Corinthians' && App\Bible\BibleBooks::normalize('Ps') === 'Psalms' && App\Bible\BibleBooks::normalize('Hezekiah') === null, 'Book names are normalized for the facet.');
$check(!array_key_exists('faith', SymbolFinder::questions()), 'Crosses are on most stones, so religious symbols do not open the gate.');

$kernel = new App\Kernel('test', true);
$kernel->boot();
$services = $kernel->getContainer()->get('test.service_container');
$em = $services->get('doctrine')->getManager();

$marker = 'Symboltest'.bin2hex(random_bytes(4));
$item = new Item($marker.' gravestone');
$item->marking = 'review';
$image = new Image($item, 0, 'stone.jpg', 'aa/'.$marker, 'image/jpeg', hash('sha256', $marker));
$image->aiResults[SymbolFinder::RESULT] = ['items' => [['name' => 'Crossed hockey sticks '.$marker, 'category' => 'sport', 'meaning' => 'a hockey player']],
    'scripture' => [['text' => 'Let the little children come to me', 'citation' => 'Matt. 19:14', 'book' => 'Matthew']]];
$item->tags = SymbolFinder::tags($item);
$check($item->tags === ['crossed hockey sticks '.strtolower($marker), 'scripture'], 'Tags are the lower-case symbol names, plus "scripture" when a quote was found.');
$em->persist($item);
$em->flush();
try {
    $tools = $services->get(MemorialTools::class);
    $hits = $tools->search('hockey sticks '.$marker);
    $check(count($hits) === 1 && $hits[0]['id'] === $item->id, 'Search finds a record by a symbol on its stone.');
    $record = $tools->get($item->id);
    $check(($record['photos'][0]['symbols'][0]['category'] ?? null) === 'sport', 'The record lists the symbols of each photo.');
    $check(($record['photos'][0]['scripture'][0]['book'] ?? null) === 'Matthew' && in_array('scripture', $record['tags'], true), 'The record lists scripture with its book, and its tags.');
    $check(count($tools->search('little children come')) >= 1, 'Search finds a record by the words of its scripture.');
    $document = App\Search\ItemDocument::fromItem($item);
    $check($document->scriptureBooks === ['Matthew'] && $document->symbolCategories === ['sport'] && in_array('crossed hockey sticks '.strtolower($marker), $document->symbols, true), 'The search index gets symbol, category and book facets.');
} finally {
    $em->remove($em->find(Item::class, $item->id));
    $em->flush();
}

echo "symbol-finder: ok\n";
