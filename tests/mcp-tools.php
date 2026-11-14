<?php

declare(strict_types=1);

use App\Entity\{Image, Item};
use App\Mcp\MemorialTools;

require dirname(__DIR__).'/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$kernel = new App\Kernel('test', true);
$kernel->boot();
$services = $kernel->getContainer()->get('test.service_container');
$em = $services->get('doctrine')->getManager();
$check = static function (bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); };

$marker = 'Mcptest'.bin2hex(random_bytes(4));
$item = new Item($marker.' gravestone');
$item->marking = 'review';
$item->aiResults['catalog']['record'] = ['people' => [['name' => $marker, 'birth' => '1900', 'death' => null, 'inscription' => null]], 'uncertainties' => ['Death date not carved.']];
$image = new Image($item, 0, 'stone.jpg', 'aa/'.$marker, 'image/jpeg', hash('sha256', $marker));
$image->sourceUrl = 'https://example.org/'.$marker.'.jpg';
$image->ocrText = 'IN MEMORY OF '.strtoupper($marker);
$em->persist($item);
$em->flush();

try {
    $tools = $services->get(MemorialTools::class);
    $hits = $tools->search(strtolower($marker));
    $check(count($hits) === 1 && $hits[0]['id'] === $item->id, 'Search finds a record by its title or OCR text.');
    $check(!in_array($item->id, array_column($tools->search('%_'), 'id'), true), 'LIKE wildcards in a query are matched literally.');
    $check(str_ends_with($hits[0]['url'], '/items/'.$item->id), 'Results link to the item page.');

    $record = $tools->get($item->id);
    $check($record['proposedPeople'][0]['name'] === $marker && $record['uncertainties'] === ['Death date not carved.'], 'The record carries proposed people and open questions.');
    $check(str_contains($record['note'], 'Not a verified burial record'), 'Every record says it is an AI proposal.');
    $check(count($record['photos']) === 1 && $record['photos'][0]['ocrText'] === $image->ocrText && $record['photos'][0]['aiCleanedView'] === null, 'Photos list OCR and no cleaned view when none exists.');

    try {
        $tools->get(2_000_000_000);
        throw new LogicException('Unknown id accepted.');
    } catch (Mcp\Exception\ToolCallException $e) {
        $check(str_contains($e->getMessage(), 'search_memorials'), 'An unknown id tells the client how to find one.');
    }

    $check(in_array($item->id, array_column($tools->toReview(50), 'id'), true) || count($tools->toReview(50)) === 50, 'Records in review are listed.');
    $overview = $tools->overview();
    $check(isset($overview['recordsByStatus']['review'], $overview['aiCleanedViews']), 'The overview counts records and cleaned views.');
} finally {
    $em->remove($item);
    $em->flush();
    $kernel->shutdown();
}
echo "MCP tools: search, record, review list, overview and errors passed.\n";
