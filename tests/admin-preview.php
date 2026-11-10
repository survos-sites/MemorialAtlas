<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
$action = 'index';
$twig = new Twig\Environment(new Twig\Loader\FilesystemLoader(dirname(__DIR__).'/templates'), ['strict_variables' => true]);
$twig->addFunction(new Twig\TwigFunction('ea', static function () use (&$action) { return (object) ['crud' => (object) ['currentAction' => $action]]; }));
$twig->addFunction(new Twig\TwigFunction('path', static fn () => '/test'));
$item = new App\Entity\Item('Test');
$image = new App\Entity\Image($item, 0, 'photo.jpg', 'a/photo', 'image/jpeg', str_repeat('a',64));
$image->description = str_repeat('Inscription ', 20);
$audio = new App\Entity\Audio($item, 1, 'note.wav', 'a/note', 'audio/wav', str_repeat('b',64));
$audio->transcript = str_repeat('Spoken context ', 20);
foreach ([$image, $audio] as $media) {
    $action = 'index';
    $html = $twig->render('admin/field/media_preview.html.twig', ['entity'=>(object)['instance'=>$media]]);
    if (!str_contains($html, '…')) throw new RuntimeException('Index must truncate previews.');
    $action = 'detail';
    $html = $twig->render('admin/field/media_preview.html.twig', ['entity'=>(object)['instance'=>$media]]);
    if (str_contains($html, '…')) throw new RuntimeException('Detail must retain full preview text.');
}
echo "Admin image/audio previews render using the EasyAdmin 5 ea() function.\n";
