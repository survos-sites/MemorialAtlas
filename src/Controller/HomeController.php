<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\{Item, Media, Image, Audio};
use App\Service\{MediaStorage, SetupStatus};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, BinaryFileResponse};
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EntityManagerInterface $em, SetupStatus $setup): Response
    {
        return $this->render('home.html.twig', [
            'requirements' => $setup->requirements(),
            'models' => $setup->models(),
            'items' => $em->getRepository(Item::class)->findBy([], ['id' => 'DESC'], 12),
            'audio' => $em->getRepository(Audio::class)->findBy([], ['id' => 'DESC'], 12),
            'images' => $em->getRepository(Image::class)->findBy([], ['id' => 'DESC'], 12),
            'itemCount' => $em->getRepository(Item::class)->count(),
            'mediaCount' => $em->getRepository(Media::class)->count(),
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
        if ($media instanceof Image && $media->sourceUrl !== null) {
            return $this->redirect($imagor->preset($media->sourceUrl, 'large'));
        }
        $response = new BinaryFileResponse($storage->path($media));
        $response->headers->set('Content-Type', $media->mimeType);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        return $response;
    }
}
