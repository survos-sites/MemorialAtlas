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
$image=['id'=>69,'mimeType'=>'image/jpeg','sha256'=>hash('sha256',$bytes),'url'=>'https://example.org/stone.jpg','originalName'=>'stone.jpg','source'=>'Sperryville','donor'=>null,'description'=>'A marker','ocrText'=>'Example 1900–1980','latitude'=>null,'longitude'=>null,'marking'=>'processed','aiResults'=>[],'imageMetadata'=>[]];
$data=['version'=>1,'key'=>'test-sperryville','cemetery'=>['name'=>'Sperryville','locality'=>'Virginia'],'item'=>['title'=>'Test marker','description'=>'A marker','locationContext'=>null,'recordType'=>'gravestone','marking'=>'review','aiResults'=>['catalog'=>['record'=>['people'=>[]]]]],'images'=>[$image]];
$calls=[];
$http=new Symfony\Component\HttpClient\MockHttpClient(static function($method,$url)use(&$calls,&$data) {
 $calls[]=$url;
 if (!str_ends_with($url,'.json')) throw new RuntimeException('Import downloaded an image.');
 return new Symfony\Component\HttpClient\Response\MockResponse(json_encode($data,JSON_THROW_ON_ERROR));
});
$storage=new App\Service\MediaStorage($dir);
$demo=new App\Service\DemoData($em,$storage,$http,'','','','','https://example.org/manifest.json');
try {
 $item=$demo->load();
 $item->aiResults['captureGrouping']=['status'=>'proposed'];
 $services->get('twig')->render('item/show.html.twig',['item'=>$item]);
 if($item->cemetery->name!=='Sperryville'||$item->media->first()->ocrText!==$image['ocrText']||is_file($storage->path($item->media->first()))||$item->media->first()->sourceUrl!==$image['url'])throw new RuntimeException('Public demo did not preserve evidence.');
 if($demo->load()->id!==$item->id||count($calls)!==2)throw new RuntimeException('Retry is not idempotent.');
 $second=$data;$second['key']='second-marker';$data=['version'=>2,'records'=>[$data,$second]];
 if($demo->load()->id!==$item->id||$em->getRepository(App\Entity\Item::class)->count([])!==2)throw new RuntimeException('Collection import failed.');
 $data=$second;
 $data['key']='corrupted';$data['images'][0]['sha256']='invalid';
 try {$demo->load();throw new LogicException('Corrupt photo accepted.');}
 catch(RuntimeException $e){if($e->getMessage()!=='Invalid demo image reference.')throw $e;}
 $pending=new App\Entity\Image($item,1,'pending.jpg','pending/photo','image/jpeg',hash('sha256','pending'));
 $em->persist($pending);$em->flush();
 $output=new Symfony\Component\Console\Output\BufferedOutput();
 $io=new Symfony\Component\Console\Style\SymfonyStyle(new Symfony\Component\Console\Input\ArrayInput([]),$output);
 (new App\Service\ProcessingStatus($em,$services->get(App\Service\SetupStatus::class)))->report($io);
 if(!str_contains($output->fetch(),'1 photographs still need processing or retry'))throw new RuntimeException('Pending photographs not flagged.');
 if($pending->marking!=='uploaded')throw new RuntimeException('Status reporting changed processing state.');
 echo "Public demo: empty S3 credentials, remote photo without downloading bytes, saved evidence, idempotency and invalid reference rejection passed.\n";
} finally {$fs->remove($dir);}
