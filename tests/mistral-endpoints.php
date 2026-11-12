<?php

declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';

use App\Service\RustoMistralPlatform;
use Symfony\AI\Platform\Bridge\Mistral\Factory;
use Symfony\AI\Platform\Message\Content\ImageUrl;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

$calls=[];
$http=new MockHttpClient(static function (string $method,string $url,array $options) use (&$calls): JsonMockResponse {
    $body=json_decode($options['body'],true,flags:JSON_THROW_ON_ERROR);
    $headers=implode("\n",$options['headers']);
    $expected=match($url) {
        'https://api.mistral.ai/v1/ocr'=>['hosted-test-key','mistral-ocr-latest'],
        'http://127.0.0.1:8895/v1/ocr'=>['local-test-key','tiny'],
        default=>throw new RuntimeException('Unexpected endpoint: '.$url),
    };
    if ($method!=='POST' || !str_contains($headers,'Bearer '.$expected[0]) || $body['model']!==$expected[1]) {
        throw new RuntimeException('Platform endpoint, credentials or model leaked across configurations.');
    }
    $calls[]=$url;
    return new JsonMockResponse(['model'=>$body['model'],'pages'=>[['index'=>0,'markdown'=>'Inscription']]]);
});
$hosted=Factory::createPlatform('hosted-test-key',$http);
$local=(new RustoMistralPlatform($http,'http://127.0.0.1:8895/','local-test-key',''))->create();
$image=new ImageUrl('https://example.test/stone.jpg');
foreach ([[$hosted,'mistral-ocr-latest'],[$local,'tiny'],[$hosted,'mistral-ocr-latest'],[$local,'tiny']] as [$platform,$model]) {
    if ($platform->invoke($model,$image)->getResult()->getContent()->getMarkdown()!=='Inscription') throw new RuntimeException('OCR conversion failed.');
}
if (count($calls)!==4) throw new RuntimeException('Expected four independent requests.');
echo "Hosted and local Mistral coexist: endpoints, credentials and models remain isolated.\n";
