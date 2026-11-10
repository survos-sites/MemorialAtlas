<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$kernel = new App\Kernel('test', true);
$kernel->boot();
$twig = $kernel->getContainer()->get('test.service_container')->get('twig');
$media = (object) [
    'id' => 1, 'originalName' => 'stone.jpg', 'position' => 0, 'marking' => 'review',
    'latitude' => null, 'longitude' => null, 'aiResults' => [], 'imageMetadata' => [],
    'description' => "**Material:** Granite\n\n- Weathered base\n- Two names\n\n<script>alert(1)</script>\n\n[Unsafe](javascript:alert(1))",
    'ocrText' => 'JAMES <unreadable>',
];
$html = $twig->render('media/_image_show.html.twig', ['media' => $media]);
foreach (['<strong>Material:</strong>', '<li>Weathered base</li>', 'JAMES &lt;unreadable&gt;'] as $expected) {
    if (!str_contains($html, $expected)) throw new RuntimeException('Missing rendered content: '.$expected);
}
if (str_contains($html, '<script>') || str_contains($html, 'href="javascript:')) {
    throw new RuntimeException('Untrusted Markdown must not produce executable HTML.');
}
$kernel->shutdown();
echo "Observation Markdown renders formatting safely and preserves literal OCR.\n";
