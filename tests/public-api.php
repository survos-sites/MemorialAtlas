<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';

use Symfony\Component\HttpClient\HttpClient;

$http = HttpClient::create([
    'base_uri' => getenv('TEST_BASE_URL') ?: 'https://127.0.0.1:8042',
    'verify_peer' => false,
    'verify_host' => false,
    'headers' => ['Host' => getenv('TEST_HOST') ?: 'mematlas.wip', 'Accept' => 'application/ld+json'],
]);
function check(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}
// Find a processed record through the API itself, so the test also runs against production (TEST_BASE_URL).
$id = null;
for ($page = 1; $id === null && $page <= 10; ++$page) {
    $members = $http->request('GET', '/api/gravestones?page='.$page)->toArray()['member'] ?? [];
    if ($members === []) break;
    foreach ($members as $member) {
        if (isset($member['claims']['catalog']['record']) || isset($member['claims']['markerFacts']['record'])) { $id = $member['id']; break; }
    }
}
check($id !== null, 'No processed record with structured claims');
$item = $http->request('GET', '/api/gravestones/'.$id)->toArray();
check(isset($item['claims']['catalog']['record']) || isset($item['claims']['markerFacts']['record']), 'Structured claims missing');
check(isset($item['media'][0]['ocrText'], $item['media'][0]['contentUrl']), 'Evidence missing');
foreach (['storagePath', 'processingError', 'sourceUrl', 'imageUrl', 'donor', 'aiResults'] as $private) {
    check(!array_key_exists($private, $item) && !array_key_exists($private, $item['media'][0]), 'Private field exposed: '.$private);
}
$cemeteryId = $item['cemetery']['id'];
$list = $http->request('GET', '/api/gravestones?cemetery='.$cemeteryId.'&recordType=gravestone')->toArray();
check(count($list['member']) > 0 && count($list['member']) <= 30, 'Pagination missing');
foreach ($list['member'] as $record) {
    check($record['cemetery']['id'] === $cemeteryId && $record['recordType'] === 'gravestone', 'Filter failed');
}
check($http->request('GET', '/api/gravestones?cemetery=2147483647')->toArray()['totalItems'] === 0, 'Cemetery scope ignored');
foreach (['POST', 'PATCH', 'DELETE'] as $method) {
    $path = $method === 'POST' ? '/api/gravestones' : '/api/gravestones/'.$id;
    check($http->request($method, $path)->getStatusCode() === 405, 'Write operation enabled: '.$method);
}
$headers = $http->request('GET', '/api/gravestones/'.$id, ['headers' => ['Origin' => 'https://genealogy.example']])->getHeaders();
check(in_array($headers['access-control-allow-origin'][0] ?? '', ['*', 'https://genealogy.example'], true), 'Public CORS missing');
check($http->request('GET', '/api/docs', ['headers' => ['Accept' => 'text/html']])->getStatusCode() === 200, 'Documentation unavailable');
echo "Public API: claims, OCR, scoped pagination, private fields, disabled writes, CORS and docs passed.\n";
