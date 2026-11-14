<?php

declare(strict_types=1);

namespace App\Service;

/** Transport adapter: the caller supplies the explicit app-to-folio page mapping. */
final class StoneCorrectionExport
{
    public function reading(array $correction, array $pageReference, string $durableSource): array
    {
        foreach (['folioCode', 'coreCode', 'localId'] as $key) {
            if (!isset($pageReference['record'][$key]) || !is_string($pageReference['record'][$key]) || trim($pageReference['record'][$key]) === '') {
                throw new \InvalidArgumentException('An explicit folio record mapping is required.');
            }
        }
        if (!isset($pageReference['seq'], $pageReference['pageIndex']) || !is_int($pageReference['seq']) || !is_int($pageReference['pageIndex']) || $pageReference['seq'] < 1 || $pageReference['pageIndex'] < 0) {
            throw new \InvalidArgumentException('An explicit folio page mapping is required.');
        }
        if ($correction['readings'] === []) throw new \InvalidArgumentException('The corrected frame has no OCR reading.');
        $reading = $correction['readings'][array_key_last($correction['readings'])];
        $frame = $correction['frame'];
        if (!preg_match('~^(s3|https)://~', $durableSource)) throw new \InvalidArgumentException('Export requires a durable S3 or HTTPS image source.');
        $frame['source'] = $durableSource;
        $frame['page'] = $pageReference;
        return ['id' => $reading['id'], 'frame' => $frame, 'provider' => $reading['provider'], 'model' => $reading['model'], 'text' => $reading['text'], 'lines' => $reading['lines']];
    }
}
