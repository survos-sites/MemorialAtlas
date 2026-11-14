<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\{Item, Media, Image, Cemetery};
use App\Service\MediaStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, BinaryFileResponse};
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(private readonly \App\Service\ProcessingStatus $processingStatus) {}

    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $em, #[\Symfony\Component\DependencyInjection\Attribute\Autowire('%env(HOME_HERO_SHA256)%')] string $heroSha256): Response
    {
        $db = $em->getConnection();
        // The chosen photo when it is loaded and cleaned; otherwise a gravestone with an AI-cleaned view.
        $heroId = $db->fetchOne("SELECT m.id FROM media m JOIN item i ON i.id = m.item_id WHERE i.record_type = 'gravestone' AND m.kind = 'image'
            ORDER BY (m.sha256 = :sha AND m.image_metadata::jsonb -> 'cleanedView' IS NOT NULL) DESC, (m.image_metadata::jsonb -> 'cleanedView' IS NOT NULL) DESC, (i.marking = 'review') DESC, m.id DESC LIMIT 1", ['sha' => $heroSha256]);

        return $this->render('home.html.twig', [
            'pendingCount' => $this->processingStatus->pendingCount(),
            'heroImage' => $heroId === false ? null : $em->find(Image::class, (int) $heroId),
            'stats' => [
                'photos' => (int) $db->fetchOne("SELECT COUNT(*) FROM media WHERE kind = 'image'"),
                'records' => (int) $db->fetchOne("SELECT COUNT(*) FROM item WHERE marking IN ('review', 'complete')"),
                'withOcr' => (int) $db->fetchOne('SELECT COUNT(*) FROM media WHERE ocr_text IS NOT NULL'),
                'cleaned' => (int) $db->fetchOne("SELECT COUNT(*) FROM media WHERE image_metadata::jsonb -> 'cleanedView' IS NOT NULL"),
                'cemeteries' => $db->fetchFirstColumn('SELECT name FROM cemetery ORDER BY name'),
            ],
        ]);
    }

    #[Route('/about', name: 'about', methods: ['GET'])]
    public function about(): Response
    {
        return $this->render('about.html.twig');
    }

    #[Route('/drone', name: 'drone', methods: ['GET'])]
    public function drone(): Response
    {
        return $this->render('drone.html.twig');
    }

    #[Route('/cemeteries/{id}', name: 'cemetery_show', requirements: ['id' => '\\d+'])]
    public function cemetery(Cemetery $cemetery, EntityManagerInterface $em, \Symfony\Component\HttpFoundation\Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $repo = $em->getRepository(Item::class);
        $total = $repo->count(['cemetery' => $cemetery]);
        $pages = max(1, (int) ceil($total / 48));
        $page = min($page, $pages);
        return $this->render('cemetery/show.html.twig', [
            'cemetery' => $cemetery,
            'items' => $em->createQuery('SELECT i, MIN(m.originalName) AS HIDDEN captureName FROM App\\Entity\\Item i JOIN i.media m WHERE i.cemetery = :cemetery GROUP BY i.id ORDER BY captureName, i.id')
                ->setParameter('cemetery', $cemetery)->setFirstResult(($page - 1) * 48)->setMaxResults(48)->getResult(),
            'total' => $total, 'page' => $page, 'pages' => $pages,
        ]);
    }

    #[Route('/items/{id}', name: 'item_show', requirements: ['id' => '\d+'])]
    public function show(Item $item, \App\Service\ImageDelivery $images): Response
    {
        $photoPreload = null;
        foreach ($item->media as $media) {
            if ($media instanceof Image) {
                $photoPreload = $images->cleaned($media, \App\Image\ImagePreset::Detail) ?? $images->detail($media);
                break;
            }
        }
        return $this->render('item/show.html.twig', ['item' => $item, 'photoPreload' => $photoPreload]);
    }

    #[Route('/media/{id}', name: 'media_file', requirements: ['id' => '\d+'])]
    public function media(Media $media, MediaStorage $storage, \App\Service\ImageDelivery $images): Response
    {
        if ($media instanceof Image) {
            return $this->redirect($images->detail($media));
        }
        $response = new BinaryFileResponse($storage->path($media));
        $response->headers->set('Content-Type', $media->mimeType);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        return $response;
    }

    /** Full-size download through imagor: no resize, EXIF rotation applied, high quality, sent as an attachment. */
    #[Route('/media/{id}/full', name: 'media_download', requirements: ['id' => '\d+'])]
    public function download(Media $media, MediaStorage $storage, \Survos\ImagorBundle\Service\ImagorUrlBuilder $imagor): Response
    {
        if ($media instanceof Image && $media->sourceUrl !== null && !str_starts_with($media->sourceUrl, 'https://')) {
            // Characters such as "(" would break imagor's filter syntax.
            $name = preg_replace('/[^A-Za-z0-9._-]+/', '_', $media->originalName ?: 'photo.jpg');

            return $this->redirect($imagor->build($media->sourceUrl, sprintf('filters:quality(95):attachment(%s)', $name)));
        }
        $response = new BinaryFileResponse($storage->path($media));
        $response->headers->set('Content-Type', $media->mimeType);
        $response->setContentDisposition(\Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT, $media->originalName ?: 'download');
        return $response;
    }
}
