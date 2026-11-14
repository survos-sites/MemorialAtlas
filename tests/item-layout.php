<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
$http = Symfony\Component\HttpClient\HttpClient::create(['verify_peer'=>false,'verify_host'=>false]);
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$kernel = new App\Kernel('dev', true); $kernel->boot();
// A processed record whose photo went through the vision route, so every evidence stage is on the page.
$itemId = $kernel->getContainer()->get('doctrine')->getConnection()->fetchOne("SELECT i.id FROM item i JOIN media m ON m.item_id = i.id
    WHERE i.marking IN ('review', 'complete') AND m.ai_results::jsonb -> 'observe' IS NOT NULL ORDER BY i.id LIMIT 1");
$kernel->shutdown();
if ($itemId === false) throw new RuntimeException('No processed record with a visual description yet. Process some photos first.');
$html = $http->request('GET', (getenv('TEST_BASE_URL') ?: 'https://127.0.0.1:8042').'/items/'.$itemId, ['headers'=>['Host'=>getenv('TEST_HOST') ?: 'mematlas.wip']])->getContent();
$c = new Symfony\Component\DomCrawler\Crawler($html);
foreach (['.item-overview .item-photo img', '.item-overview .item-description', '#ai-history', '#ai-history .evidence-stage'] as $selector) {
    if (!$c->filter($selector)->count()) throw new RuntimeException('Missing '.$selector);
}
foreach (['OCR', 'Jev', 'Visual description', 'Catalog'] as $label) {
    if (!str_contains($c->filter('#ai-history')->text(), $label)) throw new RuntimeException('Missing evidence: '.$label);
}
echo "Item photo, description and recorded AI stages render.\n";
