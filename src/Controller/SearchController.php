<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchController extends AbstractController
{
    #[Route('/search', name: 'item_search', methods: ['GET'])]
    public function items(): Response
    {
        return $this->render('search/items.html.twig');
    }

    #[Route('/search/images', name: 'image_search', methods: ['GET'])]
    public function images(): Response
    {
        return $this->render('search/images.html.twig');
    }
}
