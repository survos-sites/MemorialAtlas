<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AssistantConnectController extends AbstractController
{
    #[Route('/chat/connect', name: 'assistant_connect', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('chat/connect.html.twig', [
            'serverName' => 'MemorialAtlas',
            'endpoint' => 'https://memorialatlas.org/mcp',
        ]);
    }
}
