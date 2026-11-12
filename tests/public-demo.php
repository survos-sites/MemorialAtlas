<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$kernel=new App\Kernel('test',true);$kernel->boot();
$services=$kernel->getContainer()->get('test.service_container');
$em=new Doctrine\ORM\EntityManager(Doctrine\DBAL\DriverManager::getConnection(['driver'=>'pdo_sqlite','memory'=>true]),$services->get('doctrine')->getManager()->getConfiguration());
(new Doctrine\ORM\Tools\SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
$fs=new Symfony\Component\Filesystem\Filesystem();
$dir=sys_get_temp_dir().'/memorialatlas-demo-test-'.bin2hex(random_bytes(6));
$photo=imagecreatetruecolor(2,2);ob_start();imagejpeg($photo);$bytes=ob_get_clean();
$image=['id'=>69,'sha256'=>hash('sha256',$bytes),'url'=>'https://example.org/stone.jpg','originalName'=>'stone.jpg','source'=>'Sperryville','donor'=>null,'description'=>'A marker','ocrText'=>'Example 1900–1980','latitude'=>null,'longitude'=>null,'marking'=>'processed','aiResults'=>[],'imageMetadata'=>[]];
$data=['version'=>1,'key'=>'test-sperryville','cemetery'=>['name'=>'Sperryville','locality'=>'Virginia'],'item'=>['title'=>'Test marker','description'=>'A marker','locationContext'=>null,'recordType'=>'gravestone','marking'=>'review','aiResults'=>['catalog'=>['record'=>['people'=>[]]]]],'images'=>[$image]];
$calls=[];$corrupt=false;
$http=new Symfony\Component\HttpClient\MockHttpClient(static function($method,$url)use(&$calls,&$data,&$corrupt,$bytes) {
 $calls[]=$url;
 return new Symfony\Component\HttpClient\Response\MockResponse(str_ends_with($url,'.json')?json_encode($data,JSON_THROW_ON_ERROR):($corrupt?'corrupt':$bytes));
});
$storage=new App\Service\MediaStorage($dir);
$demo=new App\Service\DemoData($em,$storage,$http,'','','','','https://example.org/manifest.json');
try {
 $item=$demo->load();
 if($item->cemetery->name!=='Sperryville'||$item->media->first()->ocrText!==$image['ocrText']||!is_file($storage->path($item->media->first())))throw new RuntimeException('Public demo did not preserve evidence.');
 if($demo->load()->id!==$item->id||count($calls)!==3)throw new RuntimeException('Retry is not idempotent.');
 $data['key']='corrupted';$corrupt=true;
 try {$demo->load();throw new LogicException('Corrupt photo accepted.');}
 catch(RuntimeException $e){if($e->getMessage()!=='Demo image checksum mismatch.')throw $e;}
 echo "Public demo: empty S3 credentials, local photo, saved evidence, idempotency and checksum rejection passed.\n";
} finally {$fs->remove($dir);}
