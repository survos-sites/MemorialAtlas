<?php

declare(strict_types=1);

use App\Decision\{ImageQuestions as Q, JevClient};
use App\Entity\{Image, Item};
use App\Service\ImageDecision;
use App\Workflow\MediaWorkflow;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

require dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$check = static function (bool $ok, string $why): void { if (!$ok) throw new RuntimeException($why); };
$kernel = new App\Kernel('test', true);
$kernel->boot();
$services = $kernel->getContainer()->get('test.service_container');
$live = $services->get('doctrine')->getManager();
$em = new Doctrine\ORM\EntityManager(Doctrine\DBAL\DriverManager::getConnection(['driver'=>'pdo_sqlite','memory'=>true]), $live->getConfiguration());
(new Doctrine\ORM\Tools\SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
$calls = [];
$choices = [Q::TYPE=>'gravestone', Q::ROLE=>'subject', Q::MEMORIAL=>'one', Q::QUALITY=>'usable', Q::FLOWERS=>'absent', Q::MARKER=>'unclear'];
$http = new MockHttpClient(static function ($method, $url, $options) use (&$calls, &$choices): MockResponse {
    $body = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
    $calls[] = $body;
    $answers = [];
    foreach ($body['questions'] as $id=>$q) {
        $distribution = array_fill_keys(array_keys($q['criteria']), 0.0);
        $distribution[$choices[$id]] = 1.0;
        $answers[$id] = ['type'=>'choice','choice'=>$choices[$id],'probabilities'=>$distribution,'confidence'=>0.98];
    }
    return new MockResponse(json_encode(['model'=>'jev-1.14.0','answers'=>$answers,'usage'=>['input_tokens'=>400,'output_tokens'=>60]], JSON_THROW_ON_ERROR));
});
$imageHttp = new MockHttpClient(static function (): never { throw new RuntimeException('Text route must never download an image.'); });
$decision = new ImageDecision(new JevClient($http, 'test-key', 'jev-1.14.0'), $imageHttp, $em, false);
$services->set(ImageDecision::class, $decision);
$workflow = $services->get(MediaWorkflow::class);
$item = new Item('Test inscription');
$image = new Image($item, 0, 'stone.jpg', 'ignored', 'image/jpeg', str_repeat('a',64));
$image->ocrText = 'JAN WERKMAN 1883-1929 ECHTGENOOT VAN ANJE REKER';
$em->persist($item); $em->flush();
$workflow->apply($image, MediaWorkflow::PROCESS);
$check(!$workflow->can($image, MediaWorkflow::FINISH), 'Image cannot finish before assessment.');
$workflow->apply($image, MediaWorkflow::ASSESS); $em->flush();
$check($image->marking === 'assessed', 'Real workflow transition must run the assessment listener.');
$check($image->aiResults[Q::RESULT]['route'] === Q::TEXT_ROUTE, 'Clear memorial routes through text.');
$check($image->aiResults[Q::RESULT]['visualDetailsAssessed'] === false, 'Text-only must not assert visual evidence.');
$check(!isset($calls[0]['assets']), 'Text call sends no images.');
$workflow->apply($image, MediaWorkflow::ASSESS);
$check(count($calls) === 1, 'Retry with identical evidence reuses inference.');
$image->ocrText .= ' changed';
$workflow->apply($image, MediaWorkflow::ASSESS);
$check(count($calls) === 2, 'Changed OCR invalidates cached judgment.');
$workflow->apply($image, MediaWorkflow::FINISH); $em->flush();
$id=$image->id; $em->clear(); $image=$em->find(Image::class,$id);
$check($image->aiResults[Q::TEXT_RUN]['usage']['input_tokens'] === 400, 'Usage and evidence survive Doctrine hydration.');
$answers = $image->aiResults[Q::RESULT]['answers'];
$check(Q::route($answers, true) === Q::VISION_ROUTE, 'Visual-context request always uses the image.');
$answers[Q::QUALITY]['confidence']=0.4;
$check(Q::route($answers, false) === Q::VISION_ROUTE, 'Low confidence escalates.');
$answers[Q::QUALITY]['confidence']=0.99;
$answers[Q::QUALITY]['choice']='damaged';
$answers[Q::QUALITY]['probabilities']=['usable'=>0.01,'damaged'=>0.99,'insufficient'=>0];
$check(Q::route($answers,false)===Q::VISION_ROUTE, 'Confident damaged text still escalates.');

// Exercise the current Jev assets API without transmitting a real image.
$client = new JevClient($http, 'test-key', 'jev-1.14.0');
$run=$client->evaluate(['ocrText'=>null],Q::image(),'jpeg-test-bytes');
$sent=$calls[array_key_last($calls)];
$check(isset($sent['assets'][$sent['state']['photo']]), 'Image reference must identify its asset.');
$check(!isset($run['request']['assets']), 'Do not persist base64 image bytes in result JSON.');
$check($run['answers'][Q::MARKER]['choice']==='unclear', 'Uncertain accessory is preserved, not a new burial.');

foreach ([new MockResponse('{"model":"jev-1.14.0","answers":{}}'), new MockResponse('unavailable',['http_code'=>503])] as $response) {
    $bad = new JevClient(new MockHttpClient($response),'test-key','jev-1.14.0');
    $thrown=false;
    try { $bad->evaluate(['ocrText'=>'example'], Q::text()); } catch (Throwable) { $thrown=true; }
    $check($thrown,'Invalid/provider failure must throw, not return a successful decision.');
}
$em->close(); $kernel->shutdown();
echo "Image decisions: workflow, text-only routing, cache invalidation, Doctrine persistence, images and failures passed.\n";
