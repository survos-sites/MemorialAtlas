<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$hash = password_hash('test-only-password', PASSWORD_BCRYPT);
$_ENV['ADMIN_PASSWORD_HASH'] = $_SERVER['ADMIN_PASSWORD_HASH'] = $hash;
$kernel = new App\Kernel('test', true);
$kernel->boot();
$em = $kernel->getContainer()->get('test.service_container')->get('doctrine')->getManager();
$item = new App\Entity\Item('Access test memorial');
$image = new App\Entity\Image($item, 0, 'test.jpg', 'test/preview', 'image/jpeg', hash('sha256',random_bytes(16)));
$image->description = 'A preview with text that exercises ea().';
$image->sourceUrl = 'https://example.org/test.jpg';
$em->persist($item); $em->flush();
$itemId = $item->id; $imageId = $image->id;
$kernel->shutdown();
$browser = new Symfony\Bundle\FrameworkBundle\KernelBrowser($kernel);
$browser->catchExceptions(true);
$check = static function (bool $ok,string $message): void { if (!$ok) throw new RuntimeException($message); };
foreach (['/search','/items/'.$itemId,'/login'] as $url) {
    $browser->request('GET',$url);
    $check($browser->getResponse()->getStatusCode() === 200, "$url must be public.");
}
foreach ([['GET','/admin/media'],['GET','/admin/item/'.$itemId.'/edit'],['POST','/admin/item/'.$itemId.'/delete']] as [$method,$url]) {
    $browser->request($method,$url);
    $check($browser->getResponse()->isRedirect('http://localhost/login'), "$url must require login.");
}
$crawler = $browser->request('GET','/login');
$form = $crawler->selectButton('Sign in')->form(['_username'=>'admin','_password'=>'test-only-password']);
$browser->submit($form, [], ['HTTP_ORIGIN'=>'http://localhost']);
$check($browser->getResponse()->isRedirect(), 'Valid admin login must redirect.');
$browser->request('GET','/admin/media');
$check($browser->getResponse()->getStatusCode() === 200, 'Admin media listing must render after login.');
$browser->request('GET','/admin/image/'.$imageId);
$check($browser->getResponse()->getStatusCode() === 200, 'Admin image detail must render.');
$browser->request('GET','/admin/item/'.$itemId.'/edit');
$check($browser->getResponse()->getStatusCode() === 200, 'Admin must reach editing.');
$em = $browser->getContainer()->get('doctrine')->getManager();
$fixture = $em->find(App\Entity\Item::class,$itemId);
$em->remove($fixture); $em->flush();
echo "Public search/detail, protected edit/delete routes, admin form login and media preview passed.\n";
