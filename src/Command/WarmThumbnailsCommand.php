<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Image;
use App\Service\ImageDelivery;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\{AsCommand, Option};
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/** Imagor resizes a full-size original from Hugging Face in 30 to 50 seconds; one request stores the result, so visitors get it at once. */
#[AsCommand('app:thumbnails:warm', 'Request every card thumbnail once, so Imagor has it in its result cache')]
final readonly class WarmThumbnailsCommand
{
    public function __construct(private EntityManagerInterface $em, private ImageDelivery $images, private HttpClientInterface $http) {}

    public function __invoke(SymfonyStyle $io, #[Option('Requests at the same time')] int $concurrency = 4): int
    {
        $urls = array_map(fn (Image $image): string => $this->images->card($image)['url'], $this->em->getRepository(Image::class)->findAll());
        $failed = 0;
        foreach (array_chunk($urls, max(1, $concurrency)) as $chunk) {
            $responses = array_map(fn (string $url) => $this->http->request('GET', $url, ['timeout' => 120]), $chunk);
            foreach ($responses as $i => $response) {
                try {
                    $status = $response->getStatusCode();
                    $response->getContent();
                } catch (\Throwable $e) {
                    $status = $e->getMessage();
                }
                if ($status !== 200) { $failed++; $io->warning($chunk[$i].': '.$status); }
            }
        }
        $io->success(sprintf('%d thumbnails requested, %d failed.', count($urls), $failed));

        return $failed === 0 ? 0 : 1;
    }
}
