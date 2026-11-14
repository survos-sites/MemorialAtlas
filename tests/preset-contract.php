<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';

use App\Entity\{Image, Item};
use App\Image\ImagePreset;
use App\Service\ImageDelivery;
use Symfony\Component\Yaml\Yaml;
use Symfony\UX\Image\{ImagePresets, ImageUrlGenerator};
use Symfony\UX\Image\Bridge\Imagor\ImagorProvider;

$check=static function(bool $ok,string $message): void { if (!$ok) throw new RuntimeException($message); };
$config=Yaml::parseFile(dirname(__DIR__).'/config/packages/ux_image.yaml');
$check(array_keys($config['ux_image']['presets'])===['thumb','detail'], 'Only the two browser presets may be configured.');
$presets=new ImagePresets($config['ux_image']['presets']);
$local=new ImageUrlGenerator(new ImagorProvider('http://127.0.0.1:8088'), presets:$presets);
$remote=new ImageUrlGenerator(new ImagorProvider('https://imagor.survos.com','test-key',signer:'sha256',signTruncate:40),presets:$presets);
$image=new Image(new Item('Test'),0,'test.jpg',basename(__FILE__),'image/jpeg',str_repeat('a',64));
$image->sourceUrl='s3://test-bucket/original';
$dev=new ImageDelivery($local,'dev',__DIR__);
$prod=new ImageDelivery($remote,'prod',__DIR__);
foreach ([[ImagePreset::Thumb,'400x400',80],[ImagePreset::Detail,'1600x1600',82]] as [$preset,$size,$quality]) {
    $transform='fit-in/'.$size.'/filters:quality('.$quality.'):format(webp)/';
    $check($dev->url($image,$preset)==='http://127.0.0.1:8088/unsafe/'.$transform.rawurlencode(basename(__FILE__)), 'Dev must prefer the mounted original over its S3 copy.');
    $path=$transform.rawurlencode('test-bucket/original');
    $signature=substr(rtrim(strtr(base64_encode(hash_hmac('sha256',$path,'test-key',true)),'+/','-_'),'='),0,40);
    $check($prod->url($image,$preset)==='https://imagor.survos.com/'.$signature.'/'.$path, 'Production must sign the identical canonical transform and use its remote original.');
}
try { (new ImageDelivery($local,'prod',__DIR__))->detail($image); throw new RuntimeException('Production accepted a local unsigned provider.'); }
catch (LogicException $expected) {}
$check($dev->origin()==='http://127.0.0.1:8088' && $prod->origin()==='https://imagor.survos.com','Preconnect must follow the selected provider.');
echo "Preset contract: exact transforms, local sources, production signatures and provider validation passed.\n";
