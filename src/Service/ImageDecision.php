<?php

declare(strict_types=1);

namespace App\Service;

use App\Decision\{ImageQuestions as Q, JevClient};
use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class ImageDecision
{
    public function __construct(private JevClient $jev, private HttpClientInterface $http, private EntityManagerInterface $em,
        #[Autowire('%env(bool:SCANFONY_REQUIRE_VISUAL_CONTEXT)%')] private bool $visualContext) {}

    public function assess(Image $image): void
    {
        $state = ['ocrText'=>$image->ocrText];
        $hash = hash('sha256', json_encode([$image->sha256, $state, Q::VERSION, $this->jev->model], JSON_THROW_ON_ERROR));
        $textRun = $image->aiResults[Q::TEXT_RUN] ?? null;
        if ($image->ocrText !== null && trim($image->ocrText) !== '') {
            if ($textRun === null || $textRun['inputHash'] !== $hash) {
                $textRun = $this->jev->evaluate($state, Q::text()) + ['inputHash'=>$hash];
                $image->aiResults[Q::TEXT_RUN] = $textRun;
                $this->em->flush();
            }
        } else {
            $textRun = null;
            unset($image->aiResults[Q::TEXT_RUN]);
        }
        $route = $textRun === null ? Q::VISION_ROUTE : Q::route($textRun['answers'], $this->visualContext);
        $visionEnabled = $image->item->aiResults['processingPolicy']['visionEnabled'] ?? true;
        if (!$visionEnabled) {
            if ($textRun === null) throw new \RuntimeException('OCR-only processing needs readable OCR text. Review or transcribe this image before retrying.');
            $route = Q::TEXT_ROUTE;
        }
        $run = $textRun;
        if ($route === Q::VISION_ROUTE) {
            $imageHash = hash('sha256', $hash.'|'.$image->imageUrl.'|jpeg1568-pad280-v1');
            $run = $image->aiResults[Q::IMAGE_RUN] ?? null;
            if ($run === null || $run['inputHash'] !== $imageHash) {
                if ($image->imageUrl === null) throw new \LogicException('Prepare the image before its decision transition.');
                $jpeg = $this->decisionJpeg($image->imageUrl);
                $run = $this->jev->evaluate($state, Q::image(), $jpeg) + ['inputHash'=>$imageHash];
                $image->aiResults[Q::IMAGE_RUN] = $run;
                $this->em->flush();
            }
        }
        $answers = $run['answers'];
        $review = [];
        foreach ($answers as $id=>$answer) {
            if (!Q::strong($answer) || in_array($answer['choice'], ['unclear','uncertain'], true)) $review[] = $id;
        }
        $image->aiResults[Q::RESULT] = ['protocol'=>Q::VERSION, 'inputHash'=>$hash, 'route'=>$route,
            'model'=>$run['model'], 'answers'=>$answers, 'reviewFields'=>$review,
            'visualDetailsAssessed'=>$route === Q::VISION_ROUTE, 'ocrVerified'=>false];
        // Compatible projection for the catalog input and existing image listing.
        $type = $answers[Q::TYPE];
        $image->aiResults['decision'] = ['model'=>$run['model'], 'type'=>$type['choice'],
            'confidence'=>$type['confidence'], 'probabilities'=>$type['probabilities']];
    }

    /** The bounded JPEG that Jev receives: at most 1568 px, at least 280 px. */
    public function decisionJpeg(string $url): string
    {
        $bytes = $this->http->request('GET', $url, ['max_duration'=>60])->getContent();
        $bitmap = imagecreatefromstring($bytes);
        if ($bitmap === false) throw new \RuntimeException('Cannot decode decision image.');
        $scale = min(1, 1568/max(imagesx($bitmap),imagesy($bitmap)));
        if ($scale < 1) {
            $resized = imagescale($bitmap, (int)(imagesx($bitmap)*$scale));
            imagedestroy($bitmap);
            if ($resized === false) throw new \RuntimeException('Cannot resize decision image.');
            $bitmap = $resized;
        }
        if (imagesx($bitmap) < 280 || imagesy($bitmap) < 280) {
            $canvas = imagecreatetruecolor(max(280,imagesx($bitmap)), max(280,imagesy($bitmap)));
            imagefill($canvas, 0, 0, imagecolorallocate($canvas,255,255,255));
            imagecopy($canvas,$bitmap,(int)((imagesx($canvas)-imagesx($bitmap))/2),(int)((imagesy($canvas)-imagesy($bitmap))/2),0,0,imagesx($bitmap),imagesy($bitmap));
            imagedestroy($bitmap); $bitmap=$canvas;
        }
        imageinterlace($bitmap, false);
        ob_start();
        try { imagejpeg($bitmap, null, 90); $jpeg = ob_get_contents(); }
        finally { ob_end_clean(); imagedestroy($bitmap); }
        if (!is_string($jpeg) || $jpeg === '') throw new \RuntimeException('Cannot encode decision image.');
        return $jpeg;
    }
}
