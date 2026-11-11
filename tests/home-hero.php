<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$kernel = new App\Kernel('test', true);
$kernel->boot();
$em = $kernel->getContainer()->get('test.service_container')->get('doctrine')->getManager();
$ids = [];
foreach (['gravestone', 'place_reference'] as $type) {
    $item = new App\Entity\Item('Homepage selection test');
    $item->recordType = $type;
    $image = new App\Entity\Image($item, 0, 'test.jpg', 'test/hero', 'image/jpeg', hash('sha256', random_bytes(16)));
    $em->persist($item);
    $em->flush();
    $ids[$type] = [$item->id, $image->id];
}
$kernel->shutdown();
$browser = new Symfony\Bundle\FrameworkBundle\KernelBrowser($kernel);
try {
    $crawler = $browser->request('GET', '/');
    if ($browser->getResponse()->getStatusCode() !== 200 || $crawler->filter('.hero img')->attr('src') !== '/media/'.$ids['gravestone'][1]) {
        throw new RuntimeException('The hero must select a gravestone even when a newer reference photograph exists.');
    }
} finally {
    $em = $browser->getContainer()->get('doctrine')->getManager();
    foreach ($ids as [$itemId]) {
        $em->remove($em->find(App\Entity\Item::class, $itemId));
    }
    $em->flush();
}
echo "Homepage selects a gravestone instead of a newer reference photo.\n";
