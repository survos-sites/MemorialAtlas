<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$kernel = new App\Kernel('test', true);
$kernel->boot();
$twig = $kernel->getContainer()->get('test.service_container')->get('twig');
$media = new App\Entity\Image(new App\Entity\Item(), 0, 'stone.jpg', 'aa/stone', 'image/jpeg', str_repeat('a', 64));
(new ReflectionProperty(App\Entity\Media::class, 'id'))->setValue($media, 1);
$media->marking = 'review';
$media->sourceUrl = 'https://example.org/stone.jpg';
$media->description = "**Material:** Granite\n\n- Weathered base\n- Two names\n\n<script>alert(1)</script>\n\n[Unsafe](javascript:alert(1))";
$media->ocrText = 'JAMES <unreadable>';
$html = $twig->render('media/_image_show.html.twig', ['media' => $media]);
foreach (['<strong>Material:</strong>', '<li>Weathered base</li>', 'JAMES &lt;unreadable&gt;'] as $expected) {
    if (!str_contains($html, $expected)) throw new RuntimeException('Missing rendered content: '.$expected);
}
if (str_contains($html, '<script>') || str_contains($html, 'href="javascript:')) {
    throw new RuntimeException('Untrusted Markdown must not produce executable HTML.');
}
$kernel->shutdown();
echo "Observation Markdown renders formatting safely and preserves literal OCR.\n";
