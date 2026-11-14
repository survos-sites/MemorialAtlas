<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$kernel = new App\Kernel('test', true);
$kernel->boot();
$em = $kernel->getContainer()->get('test.service_container')->get('doctrine')->getManager();
$item = new App\Entity\Item('Correction access fixture');
$image = new App\Entity\Image($item, 0, 'fixture.jpg', 'test/correction', 'image/jpeg', hash('sha256', random_bytes(16)));
$image->sourceUrl = 'https://example.com/fixture.jpg';
$em->persist($item); $em->flush();
$itemId = $item->id; $imageId = $image->id;
$kernel->shutdown();
$browser = new Symfony\Bundle\FrameworkBundle\KernelBrowser($kernel);
$check = static function (bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); };
try {
    $url = '/admin/images/'.$imageId.'/correction';
    $browser->request('GET', $url);
    $check($browser->getResponse()->isRedirect('http://localhost/login'), 'Corner editing must require admin login.');
    $browser->loginUser($browser->getContainer()->get('security.user.provider.concrete.administrators')->loadUserByIdentifier('admin'));
    $crawler = $browser->request('GET', $url);
    $check($browser->getResponse()->getStatusCode() === 200, 'Authenticated editor must render.');
    $check($crawler->filter('input[type="number"]')->count() === 8, 'Keyboard users must be able to enter all four corners.');
    $token = $crawler->filter('[data-perspective-token-value]')->attr('data-perspective-token-value');
    $points = [[0.1,0.1],[0.9,0.1],[0.9,0.9],[0.1,0.9]];
    $post = static function (array $data) use ($browser, $url): void {
        $browser->request('POST', $url, server: ['CONTENT_TYPE'=>'application/json', 'HTTP_ACCEPT'=>'application/json', 'HTTP_ORIGIN'=>'http://localhost'], content: json_encode($data, JSON_THROW_ON_ERROR));
    };
    $post(['corners'=>$points, 'token'=>'invalid']);
    $check($browser->getResponse()->getStatusCode() === 403, 'Invalid CSRF must be rejected.');
    $post(['corners'=>[[0,0],[1,1],[1,0],[0,1]], 'token'=>$token]);
    $check($browser->getResponse()->getStatusCode() === 422, 'Crossed corners must be rejected before dispatch.');
    $post(['corners'=>$points, 'token'=>$token]);
    $check($browser->getResponse()->getStatusCode() === 202, 'Valid corners should be queued.');
    $transport = $browser->getContainer()->get('messenger.transport.processing');
    $messages = $transport->getSent();
    $message = $messages[array_key_last($messages)]->getMessage();
    $check($message instanceof App\Message\ProcessItem && $message->correctionImageId === $imageId && $message->itemId === $itemId, 'Reuse ProcessItem on the processing transport.');
    $check($message->corners === $points && $message->correction === true, 'Selection and gate must survive queue transport.');
    $crawler = $browser->request('GET', $url);
    $check(json_decode($crawler->filter('[data-perspective-corners-value]')->attr('data-perspective-corners-value'), true) === $points, 'Reloaded editor must retain saved correction points.');
    $check($crawler->filter('[data-perspective-target="handle"]')->count() === 4, 'Four labelled adjustment handles must render.');
    echo "Correction access: authentication, CSRF, editor inputs, geometry rejection and existing-queue dispatch passed.\n";
} finally {
    $em = $browser->getContainer()->get('doctrine')->getManager();
    $em->remove($em->find(App\Entity\Item::class, $itemId)); $em->flush();
}
