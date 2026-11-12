<?php
// Remove after the upstream OCR base-URL fix is released; related: symfony/ai#2900.
declare(strict_types=1);
$root=dirname(__DIR__).'/vendor/symfony/ai-mistral-platform/';
$file=$root.'Ocr/ModelClient.php';
$source=file_get_contents($file);
if (!str_contains($source, '$this->baseUrl')) {
    $replacements=[
        '    public function __construct(' => "    private readonly string \$baseUrl;\n\n    public function __construct(",
        'private readonly string $apiKey,' => "private readonly string \$apiKey,\n        string \$baseUrl = 'https://api.mistral.ai',",
        "    ) {\n    }" => "    ) {\n        \$this->baseUrl = rtrim(\$baseUrl, '/');\n    }",
        "'https://api.mistral.ai/v1/ocr'" => "\$this->baseUrl.'/v1/ocr'",
    ];
    foreach ($replacements as $old=>$new) {
        if (substr_count($source,$old)!==1) throw new RuntimeException('Mistral OCR source changed; review the upstream patch before installing.');
        $source=str_replace($old,$new,$source);
    }
    file_put_contents($file,$source);
}
$file=$root.'Factory.php';$source=file_get_contents($file);
$old='new Ocr\\ModelClient($httpClient, $apiKey)';
$new='new Ocr\\ModelClient($httpClient, $apiKey, $baseUrl)';
if (str_contains($source,$old)) file_put_contents($file,str_replace($old,$new,$source));
elseif (!str_contains($source,$new)) throw new RuntimeException('Mistral factory changed; review OCR endpoint forwarding.');
