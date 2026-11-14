<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';
use App\Service\PerspectiveCorrector;
$check = static function (bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); };
$corrector = new PerspectiveCorrector();
$check(!PerspectiveCorrector::enabled(false), 'Opt-out must bypass correction.');
$check(PerspectiveCorrector::enabled(), 'Correction defaults on with OpenCV installed.');
foreach ([[[0,0],[1,1],[1,0],[0,1]], [[0,0],[0,1],[1,1],[1,0]], [[0,0],[1,0],[1,0],[0,1]], [[-1,0],[1,0],[1,1],[0,1]], [[0,0],[1,0],[1,INF],[0,1]]] as $corners) {
    try { $corrector->points($corners, 200, 100); throw new RuntimeException('Invalid geometry accepted.'); }
    catch (InvalidArgumentException) {}
}
$img = imagecreatetruecolor(201, 101);
imagefilledrectangle($img, 0, 0, 200, 100, imagecolorallocate($img, 255, 255, 255));
imagefilledrectangle($img, 30, 20, 50, 40, imagecolorallocate($img, 255, 0, 0));
ob_start(); imagepng($img); $bytes = ob_get_clean();
$result = $corrector->correct($bytes, [[0,0],[1,0],[1,1],[0,1]]);
$check($result['width'] === 200 && $result['height'] === 100, 'Full-frame output dimensions must follow pixel-center edges.');
$out = imagecreatefromstring($result['bytes']);
$color = imagecolorsforindex($out, imagecolorat($out, 40, 30));
$check($color['red'] > 220 && $color['green'] < 30, 'Warp must preserve the known marker position.');
$check($result['sourceCorners'][2] === ['x'=>200.0,'y'=>100.0], 'Record exact source frame coordinates.');
echo "Perspective: native OpenCV warp, ordered corners, bounds, opt-out and pixel placement passed.\n";
