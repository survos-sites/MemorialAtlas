<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\RustoOcr;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Test bench: one image URL through every model of the local rusto OCR service.
 * Coming from a tombstone photo (?media=ID) fills the URL in. Admin only (the /admin firewall rule),
 * because the server fetches whatever URL it is given.
 */
final class OcrLabController extends AbstractController
{
    private const MAX_BYTES = 25 * 1024 * 1024;
    private const IMAGE_TYPES = ['image/png', 'image/jpeg', 'image/webp', 'image/tiff'];

    #[Route('/admin/ocr-lab', name: 'admin_ocr_lab', methods: ['GET', 'POST'])]
    public function __invoke(Request $request, RustoOcr $ocr, HttpClientInterface $http): Response
    {
        $url = trim($request->request->getString('url', $request->query->getString('url')));
        if ('' === $url && $request->query->getInt('media') > 0) {
            $url = $this->generateUrl('media_file', ['id' => $request->query->getInt('media')], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $view = ['url' => $url, 'service' => $ocr->baseUrl(), 'models' => [], 'selected' => [], 'results' => [], 'error' => null, 'image' => null];
        try {
            $view['models'] = $ocr->models();
        } catch (\Throwable $e) {
            $view['error'] = sprintf('The OCR service at %s is not reachable (%s). Start ocr-api first.', $ocr->baseUrl(), $e->getMessage());
        }

        $picked = array_map('strval', (array) $request->request->all('models'));
        $view['selected'] = in_array('all', $picked, true) || !$request->isMethod('POST') ? $view['models'] : array_values(array_intersect($view['models'], $picked));
        $view['all'] = !$request->isMethod('POST') || in_array('all', $picked, true);

        if (!$request->isMethod('POST') || '' === $url || null !== $view['error']) {
            return $this->render('admin/ocr_lab.html.twig', $view);
        }

        try {
            [$bytes, $mime] = $this->fetchImage($http, $url);
        } catch (\Throwable $e) {
            return $this->render('admin/ocr_lab.html.twig', ['error' => $e->getMessage()] + $view);
        }
        $view['image'] = sprintf('data:%s;base64,%s', $mime, base64_encode($bytes));
        foreach ($view['selected'] as $model) {
            $view['results'][] = $ocr->recognize($bytes, $mime, $model);
        }

        return $this->render('admin/ocr_lab.html.twig', $view);
    }

    /** @return array{string, string} bytes and mime type */
    private function fetchImage(HttpClientInterface $http, string $url): array
    {
        if (!preg_match('#^https?://#i', $url)) {
            throw new \InvalidArgumentException('The URL must start with http:// or https://.');
        }
        $response = $http->request('GET', $url, ['timeout' => 30, 'max_redirects' => 5]);
        $bytes = '';
        foreach ($http->stream($response) as $chunk) {
            $bytes .= $chunk->getContent();
            if (strlen($bytes) > self::MAX_BYTES) {
                throw new \RuntimeException('The image is larger than 25 MB.');
            }
        }
        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
        if (!in_array($mime, self::IMAGE_TYPES, true)) {
            throw new \RuntimeException(sprintf('Not a supported image (%s); PNG, JPEG, WebP and TIFF only.', $mime ?: 'unknown type'));
        }

        return [$bytes, $mime];
    }
}
