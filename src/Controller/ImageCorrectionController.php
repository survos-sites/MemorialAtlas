<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Image;
use App\Image\CorrectionRequest;
use App\Message\ProcessItem;
use App\Service\{ImageCorrection, PerspectiveCorrector, ImageDelivery};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, JsonResponse, BinaryFileResponse};
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Messenger\MessageBusInterface;

final class ImageCorrectionController extends AbstractController
{
    #[Route('/admin/images/{id}/correction', name: 'image_correction', methods: ['GET'])]
    public function edit(Image $image): Response
    {
        return $this->render('media/correct.html.twig', ['media' => $image, 'opencv' => extension_loaded('opencv')]);
    }

    #[Route('/admin/images/{id}/correction', name: 'image_correction_save', methods: ['POST'], format: 'json')]
    public function save(Image $image, #[MapRequestPayload] CorrectionRequest $input, PerspectiveCorrector $corrector, EntityManagerInterface $em, MessageBusInterface $bus): JsonResponse
    {
        if (!$this->isCsrfTokenValid('correct-'.$image->id, $input->token)) throw $this->createAccessDeniedException('Invalid correction token.');
        PerspectiveCorrector::enabled();
        try { $corrector->points($input->corners, 1600, 1600); }
        catch (\InvalidArgumentException $error) { return $this->json(['error' => $error->getMessage()], 422); }
        $image->imageMetadata['correctionCorners'] = $input->corners;
        $image->imageMetadata['correctionStatus'] = 'queued';
        $em->flush();
        $bus->dispatch(new ProcessItem($image->item->id, correction: true, correctionImageId: $image->id, corners: $input->corners));
        return $this->json(['url' => $this->generateUrl('item_show', ['id' => $image->item->id]).'#media-'.$image->id], 202);
    }

    #[Route('/media/{id}/corrections/{correctionId}/{frame}', name: 'image_correction_file', requirements: ['correctionId' => '[a-f0-9]{64}', 'frame' => 'original|corrected'], methods: ['GET'])]
    public function rendition(Image $image, string $correctionId, string $frame, ImageCorrection $corrections): Response
    {
        try { $entry = $corrections->get($image, $correctionId); }
        catch (\InvalidArgumentException) { throw $this->createNotFoundException(); }
        $source = $entry[$frame === 'original' ? 'parentFrame' : 'frame']['source'];
        $response = new BinaryFileResponse($corrections->path($source));
        $response->setPublic()->setMaxAge(31536000)->setImmutable();
        $response->headers->set('Content-Type', $frame === 'original' ? 'image/webp' : 'image/jpeg');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        return $response;
    }
}
