<?php

declare(strict_types=1);

$loader = require dirname(__DIR__).'/vendor/autoload.php';
$contract = $argv[1] ?? '/Users/tac/sites/mono/lib/stone-contract';
$loader->addPsr4('Survos\\StoneContract\\', $contract.'/src');
$loader->addPsr4('Survos\\DataContracts\\', dirname($contract).'/data-contracts/src');
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__).'/.env');
$kernel = new App\Kernel('dev', true); $kernel->boot();
$em = $kernel->getContainer()->get('doctrine')->getManager();
$image = $em->find(App\Entity\Image::class, 119);
$entry = $image->imageMetadata[App\Service\ImageCorrection::KEY][$image->imageMetadata['activeCorrection']];
$originalOcr = $image->aiResults['ocr']['raw']['text'];
$check = static function (bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); };
$mapping = ['record'=>['folioCode'=>'test/cemetery', 'coreCode'=>Survos\DataContracts\Vocabulary\Core::OBJECT, 'localId'=>'source-stone'], 'seq'=>1, 'pageIndex'=>0];
$data = (new App\Service\StoneCorrectionExport())->reading($entry, $mapping, 's3://test/corrected.jpg');
use Survos\StoneContract\{RecordReference, PageReference, PixelPoint, PerspectiveCorrection, ImageFrame, OcrLine, OcrReading};
$reference = new RecordReference(...$data['frame']['page']['record']);
$page = new PageReference($reference, $mapping['seq'], $mapping['pageIndex']);
$frame = $data['frame'];
$perspective = new PerspectiveCorrection($frame['perspective']['parentFrameId'], array_map(static fn (array $p) => new PixelPoint(...$p), $frame['perspective']['sourceCorners']));
$typedFrame = new ImageFrame($frame['id'], $page, $frame['source'], $frame['width'], $frame['height'], $frame['sha256'], $perspective);
$lines = array_map(static fn (array $line) => new OcrLine($line['id'], $line['text'], array_map(static fn (array $p) => new PixelPoint(...$p), $line['polygon']), $line['confidence']), $data['lines']);
$reading = new OcrReading($data['id'], $typedFrame, $data['provider'], $data['text'], $lines, $data['model']);
$check(count($reading->lines) === 4, 'The sample corrected image must retain its four OCR lines.');
$check($reading->frame->sha256 === hash_file('sha256', dirname(__DIR__).'/var/media/'.$entry['frame']['source']), 'OCR frame must identify the actual saved derivative.');
$check($entry['parentFrame']['sha256'] === hash_file('sha256', dirname(__DIR__).'/var/media/'.$entry['parentFrame']['source']), 'Keep the exact uncorrected source frame.');
$check($image->sha256 === $entry['originalSha256'] && str_contains($originalOcr, 'FEB.9. 1919'), 'Original photograph identity and OCR remain unchanged.');
$check($entry['readings'][0]['receipt']['orientation_corrected'] === false, 'OCR must not silently rotate the derivative.');
echo "Correction evidence: real saved frame hashes, original evidence, hosted OCR geometry and Stone-contract hydration passed.\n";
