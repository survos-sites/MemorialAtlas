<?php

declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
$dir=sys_get_temp_dir().'/capture-manifest-'.bin2hex(random_bytes(6));mkdir($dir);
try {
 file_put_contents($dir.'/a.jpg','test-photo');
 $data=['version'=>1,'name'=>'Test','context'=>'Test context','groupingMethod'=>'manual_review','groups'=>[['files'=>['a.jpg'],'basis'=>'Overview']],'sha256'=>['a.jpg'=>hash('sha256','test-photo')]];
 $save=static function(array $data)use($dir){file_put_contents($dir.'/manifest.json',json_encode($data,JSON_THROW_ON_ERROR));};
 $save($data);$loaded=App\Service\CaptureManifest::load($dir.'/manifest.json',$dir);
 if($loaded!==$data)throw new RuntimeException('Manifest changed during validation.');
 foreach(['checksum','duplicate','traversal','missing'] as $case){
  $bad=$data;
  if($case==='checksum')$bad['sha256']['a.jpg']=str_repeat('0',64);
  if($case==='duplicate')$bad['groups'][]=$bad['groups'][0];
  if($case==='traversal')$bad['groups'][0]['files']=['../a.jpg'];
  if($case==='missing')$bad['groups'][0]['files']=['missing.jpg'];
  $save($bad);$rejected=false;
  try{App\Service\CaptureManifest::load($dir.'/manifest.json',$dir);}catch(InvalidArgumentException){$rejected=true;}
  if(!$rejected)throw new RuntimeException('Accepted invalid manifest: '.$case);
 }
 echo "Capture manifest: checksums, duplicate membership, traversal and missing files passed.\n";
}finally{unlink($dir.'/a.jpg');unlink($dir.'/manifest.json');rmdir($dir);}
