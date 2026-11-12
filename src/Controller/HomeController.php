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
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('home.html.twig', [
            'pendingCount' => $this->processingStatus->pendingCount(),
            'heroImage' => $em->createQueryBuilder()
                ->select('image')
                ->from(Image::class, 'image')
                ->join('image.item', 'item')
                ->where('item.recordType = :recordType')
                ->setParameter('recordType', 'gravestone')
                ->orderBy('image.id', 'DESC')
                ->setMaxResults(1)
                ->getQuery()->getOneOrNullResult(),
        ]);
    }

    #[Route('/about', name: 'about', methods: ['GET'])]
    public function about(): Response
    {
        return $this->render('about.html.twig');
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
    public function show(Item $item): Response
    {
        return $this->render('item/show.html.twig', ['item' => $item]);
    }

    #[Route('/media/{id}', name: 'media_file', requirements: ['id' => '\d+'])]
    public function media(Media $media, MediaStorage $storage, \Survos\ImagorBundle\Service\ImagorUrlBuilder $imagor): Response
    {
        if ($media instanceof Image && str_starts_with($media->sourceUrl ?? '', 'https://')) {
            return $this->redirect($media->sourceUrl);
        }
        if ($media instanceof Image && $media->sourceUrl !== null) {
            return $this->redirect($imagor->preset($media->sourceUrl, 'large'));
        }
        $response = new BinaryFileResponse($storage->path($media));
        $response->headers->set('Content-Type', $media->mimeType);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        return $response;
    }
}
