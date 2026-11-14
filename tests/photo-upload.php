<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$check = static function (bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); };
// A real JPEG APP1 segment with latitude 38 N, longitude 77 W, and Orientation 6.
$entry = static fn ($tag, $type, $count, $value) => pack('vvV', $tag, $type, $count).$value;
$tiff = 'II'.pack('vV', 42, 8).pack('v', 2)
    .$entry(0x112, 3, 1, pack('vxx', 6)).$entry(0x8825, 4, 1, pack('V', 38)).pack('V', 0)
    .pack('v', 4).$entry(1, 2, 2, "N\0\0\0").$entry(2, 5, 3, pack('V', 92))
    .$entry(3, 2, 2, "W\0\0\0").$entry(4, 5, 3, pack('V', 116)).pack('V', 0)
    .pack('V*', 38, 1, 0, 1, 0, 1, 77, 1, 0, 1, 0, 1);
$gd = imagecreatetruecolor(100, 100);
imagesetpixel($gd, 0, 0, random_int(0, 0xffffff));
ob_start(); imagejpeg($gd); $jpeg = ob_get_clean();
$segment = "Exif\0\0".$tiff;
$bytes = substr($jpeg, 0, 2)."\xff\xe1".pack('n', strlen($segment) + 2).$segment.substr($jpeg, 2);
$fixture = tempnam(sys_get_temp_dir(), 'upload-'); file_put_contents($fixture, $bytes);
$plain = tempnam(sys_get_temp_dir(), 'upload-'); file_put_contents($plain, $jpeg);
$bad = tempnam(sys_get_temp_dir(), 'upload-'); file_put_contents($bad, '<?php echo "not a photograph";');
$kernel = new App\Kernel('test', true); $kernel->boot();
$em = $kernel->getContainer()->get('test.service_container')->get('doctrine')->getManager();
$cemetery = new App\Entity\Cemetery(); $cemetery->name = 'Upload fixture '.bin2hex(random_bytes(4));
$cemetery->latitude = 38; $cemetery->longitude = -77; $cemetery->matchingRadiusMeters = 50;
$em->persist($cemetery); $em->flush(); $cemeteryId = $cemetery->id;
$kernel->shutdown();
$browser = new Symfony\Bundle\FrameworkBundle\KernelBrowser($kernel);
$itemIds = []; $paths = []; $otherId = null;
try {
    $browser->request('GET', '/admin/photos/upload');
    $check($browser->getResponse()->isRedirect(), 'Upload form requires login.');
    $browser->request('POST', '/admin/photos/upload');
    $check($browser->getResponse()->isRedirect(), 'Upload endpoint requires login.');
    $browser->loginUser($browser->getContainer()->get('security.user.provider.concrete.administrators')->loadUserByIdentifier('admin'));
    $crawler = $browser->request('GET', '/admin/photos/upload');
    $check($browser->getResponse()->getStatusCode() === 200, 'Upload form renders.');
    $token = $crawler->filter('input[name="token"]')->attr('value');
    $post = static function (array $data, ?string $file) use ($browser): array {
        $files = $file === null ? [] : ['photo' => new Symfony\Component\HttpFoundation\File\UploadedFile($file, 'marker.jpg', 'image/jpeg', null, true)];
        $browser->request('POST', '/admin/photos/upload', $data, $files, ['HTTP_ACCEPT'=>'application/json', 'HTTP_ORIGIN'=>'http://localhost']);
        return json_decode($browser->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
    };
    $post(['token'=>'invalid', 'cemeteryId'=>$cemeteryId], $fixture);
    $check($browser->getResponse()->getStatusCode() === 403, 'Invalid CSRF rejected.');
    $post(['token'=>$token], null);
    $check($browser->getResponse()->getStatusCode() === 422, 'Missing photo rejected.');
    $post(['token'=>$token], $bad);
    $check($browser->getResponse()->getStatusCode() === 422, 'Fake JPEG rejected.');
    $post(['token'=>$token], $plain);
    $check($browser->getResponse()->getStatusCode() === 422, 'No GPS requires explicit cemetery.');
    $post(['token'=>$token, 'cemeteryId'=>2147483647], $fixture);
    $check($browser->getResponse()->getStatusCode() === 422, 'Unknown cemetery rejected.');
    $post(['token'=>$token, 'cemeteryId'=>$cemeteryId, 'corners'=>[[0,0],[1,1],[1,0],[0,1]]], $fixture);
    $check($browser->getResponse()->getStatusCode() === 422, 'Invalid corners rejected.');
    $em = $browser->getContainer()->get('doctrine')->getManager();
    $other = new App\Entity\Cemetery(); $other->name = 'Overlapping test area'; $other->latitude = 38; $other->longitude = -77; $other->matchingRadiusMeters = 50;
    $em->persist($other); $em->flush(); $otherId = $other->id;
    $result = $post(['token'=>$token], $fixture);
    $check($browser->getResponse()->getStatusCode() === 422 && count($result['candidates']) >= 2, 'Overlapping GPS areas require selection.');
    $em = $browser->getContainer()->get('doctrine')->getManager(); $em->remove($em->find(App\Entity\Cemetery::class, $otherId)); $em->flush(); $otherId = null;
    $matcher = new App\Service\CemeteryMatcher();
    $check($matcher->matches([$cemetery], 0, 0) === [], 'A distant cemetery is not a match.');
    $points = [[0.1,0.1],[0.9,0.1],[0.9,0.9],[0.1,0.9]];
    $result = $post(['token'=>$token, 'corners'=>$points], $fixture);
    $check($browser->getResponse()->getStatusCode() === 202, 'GPS upload should be accepted: '.json_encode($result));
    $itemIds[] = $result['itemId'];
    $check($result['cemeteryId'] === $cemeteryId && abs($result['gps']['latitude'] - 38) < 1e-8 && abs($result['gps']['longitude'] + 77) < 1e-8, 'Original EXIF determines GPS and cemetery.');
    $check(!$result['needsCorners'], 'Supplied corners retained.');
    $check($browser->getResponse()->headers->get('Location') === $result['statusUrl'], '202 points to status endpoint.');
    $messages = $browser->getContainer()->get('messenger.transport.processing')->getSent();
    $check(count($messages) === 1 && $messages[0]->getMessage() instanceof App\Message\ProcessItem && $messages[0]->getMessage()->itemId === $result['itemId'] && $messages[0]->getMessage()->correction === true, 'Existing processing handler receives default correction gate.');
    $em = $browser->getContainer()->get('doctrine')->getManager();
    $image = $em->find(App\Entity\Image::class, $result['imageId']);
    $path = $browser->getContainer()->get(App\Service\MediaStorage::class)->path($image); $paths[] = $path;
    $check(file_get_contents($path) === $bytes && $image->imageMetadata['exif']['Orientation'] === 6, 'Exact original bytes and camera orientation preserved.');
    App\Service\CaptureMetadata::apply($image, [], 'Imagor EXIF GPS');
    $check($image->latitude === 38.0 && $image->longitude === -77.0, 'Absent derivative EXIF must not erase GPS.');
    $browser->request('GET', $result['statusUrl']);
    $check($browser->getResponse()->getStatusCode() === 200 && str_contains($browser->getResponse()->headers->get('Cache-Control'), 'no-store'), 'Private status available.');
    $post(['token'=>$token, 'cemeteryId'=>$cemeteryId], $fixture);
    $check($browser->getResponse()->getStatusCode() === 409 && count($browser->getContainer()->get('messenger.transport.processing')->getSent()) === 0, 'Duplicate does not queue again.');
    $result = $post(['token'=>$token, 'cemeteryId'=>$cemeteryId, 'correction'=>'0'], $plain);
    $check($browser->getResponse()->getStatusCode() === 202, 'Explicit cemetery allows photos without GPS.');
    $itemIds[] = $result['itemId'];
    $messages = $browser->getContainer()->get('messenger.transport.processing')->getSent();
    $check($messages[0]->getMessage()->correction === false, 'Correction opt-out survives transport.');
    $image = $browser->getContainer()->get('doctrine')->getManager()->find(App\Entity\Image::class, $result['imageId']);
    $paths[] = $browser->getContainer()->get(App\Service\MediaStorage::class)->path($image);
    echo "Upload: auth, CSRF, file validation, EXIF GPS, matching, ambiguity, corners, original storage, queue dispatch, duplicate prevention and opt-out passed.\n";
} finally {
    $em = $browser->getContainer()->get('doctrine')->getManager();
    foreach ($itemIds as $id) $em->remove($em->find(App\Entity\Item::class, $id));
    $em->flush();
    if ($otherId !== null) $em->remove($em->find(App\Entity\Cemetery::class, $otherId));
    $em->remove($em->find(App\Entity\Cemetery::class, $cemeteryId)); $em->flush();
    foreach (array_merge([$fixture,$plain,$bad], $paths) as $path) if (is_file($path)) unlink($path);
}
