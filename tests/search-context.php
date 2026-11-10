<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$kernel = new App\Kernel('test',true); $kernel->boot();
$manager = $kernel->getContainer()->get('test.service_container')->get('doctrine')->getManager();
$em = new Doctrine\ORM\EntityManager(Doctrine\DBAL\DriverManager::getConnection(['driver'=>'pdo_sqlite','memory'=>true]), $manager->getConfiguration());
(new Doctrine\ORM\Tools\SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
$directory = sys_get_temp_dir().'/stonereader-spool-test-'.bin2hex(random_bytes(6));
$spool = new Survos\ElasticBundle\Spool\ElasticSpooler($directory);
$listener = new App\EventListener\SearchContextListener($spool);
$em->getEventManager()->addEventListener(['onFlush','postFlush','onClear'],$listener);
$cemetery = new App\Entity\Cemetery(); $cemetery->name='Test cemetery';
$item = new App\Entity\Item('Test record'); $item->cemetery=$cemetery;
$image = new App\Entity\Image($item,0,'test.jpg','test/photo','image/jpeg',str_repeat('c',64));
$em->persist($cemetery); $em->persist($item); $em->flush();
$checkQueued = static function () use ($spool,$image): void {
    $ids = $spool->pendingIds(App\Entity\Image::class);
    if (array_map('strval',$ids) !== [(string)$image->id]) throw new RuntimeException('Parent change must enqueue its image.');
    $spool->drain(App\Entity\Image::class, static fn (array $ids) => count($ids));
};
try {
    $spool->drain(App\Entity\Item::class, static fn (array $ids) => count($ids));
    $image->ocrText='Corrected inscription'; $em->flush();
    if (array_map('strval', $spool->pendingIds(App\Entity\Item::class)) !== [(string)$item->id]) throw new RuntimeException('Media edits must enqueue their aggregate item.');
    $spool->drain(App\Entity\Item::class, static fn (array $ids) => count($ids));
    $item->title='Corrected name'; $em->flush(); $checkQueued();
    $cemetery->name='Corrected cemetery'; $em->flush(); $checkQueued();
    if (array_map('strval', $spool->pendingIds(App\Entity\Item::class)) !== [(string)$item->id]) throw new RuntimeException('Cemetery edits must enqueue aggregate items.');
    $em->remove($item); $em->flush();
    if (count($spool->pendingIds(App\Entity\Image::class)) !== 1) throw new RuntimeException('Parent deletion must enqueue image removal.');
} finally {
    (new Symfony\Component\Filesystem\Filesystem())->remove($directory);
    $kernel->shutdown();
}
echo "Parent edits, cemetery edits and deletion queue affected search images.\n";
