<?php
require dirname(__DIR__).'/vendor/autoload.php';
use App\Service\CorrectedImageOcr;
$frame = ['width'=>200,'height'=>100];
$raw = ['model'=>'test-model','pages'=>[['dimensions'=>$frame,'markdown'=>"NAME\nDATE",'blocks'=>[['top_left_x'=>10,'top_left_y'=>20,'bottom_right_x'=>180,'bottom_right_y'=>80,'content'=>"NAME\nDATE"]]]]];
$reading=CorrectedImageOcr::reading($raw,$frame);
if (count($reading['lines'])!==1 || $reading['lines'][0]['text']!=="NAME\nDATE" || $reading['lines'][0]['confidence']!==null || $reading['receipt']!==$raw) throw new RuntimeException('Preserve whole block, unknown confidence and raw evidence.');
foreach (['dimensions','bounds'] as $invalid) {
    $bad=$raw;
    if ($invalid==='dimensions') $bad['pages'][0]['dimensions']['width']=100;
    else $bad['pages'][0]['blocks'][0]['bottom_right_x']=201;
    try { CorrectedImageOcr::reading($bad,$frame); } catch (RuntimeException) { continue; }
    throw new RuntimeException('Invalid geometry accepted.');
}
unset($raw['pages'][0]['blocks']);
if (CorrectedImageOcr::reading($raw,$frame)['lines']!==[]) throw new RuntimeException('Do not fabricate missing boxes.');
echo "Mistral blocks: preserved evidence, missing geometry and invalid frame checks passed.\n";
