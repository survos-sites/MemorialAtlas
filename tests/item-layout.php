<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
$http = Symfony\Component\HttpClient\HttpClient::create(['verify_peer'=>false,'verify_host'=>false]);
$html = $http->request('GET', 'https://127.0.0.1:8042/items/69', ['headers'=>['Host'=>'scanfony.wip']])->getContent();
$c = new Symfony\Component\DomCrawler\Crawler($html);
foreach (['.item-overview .item-photo img', '.item-overview .item-description', '#ai-history', '#ai-history .evidence-stage'] as $selector) {
    if (!$c->filter($selector)->count()) throw new RuntimeException('Missing '.$selector);
}
foreach (['OCR', 'Jev', 'Visual description', 'Catalog'] as $label) {
    if (!str_contains($c->filter('#ai-history')->text(), $label)) throw new RuntimeException('Missing evidence: '.$label);
}
echo "Item photo, description and recorded AI stages render.\n";
