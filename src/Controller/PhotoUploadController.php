<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\{Cemetery, Image, Item, Media};
use App\Message\ProcessItem;
use App\Service\{CaptureMetadata, CemeteryMatcher, MediaStorage, PerspectiveCorrector, PhotoMetadata};
use App\Upload\PhotoUploadRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Response};
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class PhotoUploadController extends AbstractController
{
    #[Route('/admin/photos/upload', name: 'photo_upload_form', methods: ['GET'])]
    public function form(EntityManagerInterface $em): Response
    {
        return $this->render('upload/photo.html.twig', ['cemeteries' => $em->getRepository(Cemetery::class)->findBy([], ['name' => 'ASC'])]);
    }

    #[Route('/admin/photos/upload', name: 'photo_upload', methods: ['POST'], format: 'json')]
    public function upload(#[MapRequestPayload(acceptFormat: 'form')] PhotoUploadRequest $input, EntityManagerInterface $em, MediaStorage $storage, CemeteryMatcher $matcher, PerspectiveCorrector $corrector, MessageBusInterface $bus): JsonResponse
    {
        if (!$this->isCsrfTokenValid('photo-upload', $input->token)) throw $this->createAccessDeniedException('Invalid upload token.');
        if (!extension_loaded('exif')) return $this->json(['error' => 'Install the PHP EXIF extension before accepting photographs.'], 503);
        $correction = $input->correction;
        $correction ??= true;
        $corners = $input->normalizedCorners();
        try {
            PerspectiveCorrector::enabled($correction);
            if ($corners !== null) {
                if (!$correction) throw new \InvalidArgumentException('Corners require correction to be enabled.');
                $corrector->points($corners, 1600, 1600);
            }
        } catch (\InvalidArgumentException $error) {
            return $this->json(['error' => $error->getMessage()], 422);
        } catch (\RuntimeException $error) {
            return $this->json(['error' => $error->getMessage().' For uploads, set correction=0.'], 503);
        }
        $photo = $input->photo;
        assert($photo !== null);
        $exif = PhotoMetadata::readExif($photo->getPathname());
        [$latitude, $longitude] = CaptureMetadata::coordinates($exif);
        $hash = hash_file('sha256', $photo->getPathname());
        if ($input->cemeteryId !== null) {
            $cemetery = $em->find(Cemetery::class, $input->cemeteryId);
            if ($cemetery === null) return $this->json(['error' => 'Unknown cemetery.'], 422);
        } else {
            $matches = $matcher->matches($em->getRepository(Cemetery::class)->findAll(), $latitude, $longitude);
            if (count($matches) !== 1) return $this->json([
                'error' => 'Select a cemetery: GPS is absent, outside configured areas, or matches more than one cemetery.',
                'gps' => ['latitude' => $latitude, 'longitude' => $longitude],
                'candidates' => array_map(static fn (Cemetery $c) => ['id' => $c->id, 'name' => $c->name], $matches),
            ], 422);
            $cemetery = $matches[0];
        }
        // Serialize retries for the same bytes; the queue insert shares this Doctrine transaction.
        return $em->wrapInTransaction(function () use ($em, $storage, $bus, $photo, $exif, $cemetery, $input, $correction, $hash, $corners): JsonResponse {
            $em->getConnection()->executeQuery('SELECT pg_advisory_xact_lock(hashtextextended(?, 0))', [$hash]);
            $existing = $em->getRepository(Media::class)->findOneBy(['sha256' => $hash]);
            if ($existing !== null) return $this->json(['error' => 'This photograph has already been imported.', 'itemId' => $existing->item->id, 'url' => $this->generateUrl('item_show', ['id' => $existing->item->id])], 409);
            $item = new Item(mb_substr(pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME), 0, 255));
            $item->cemetery = $cemetery;
            $item->aiResults['processingPolicy']['correction'] = $correction;
            $image = $storage->import($item, $photo->getPathname(), $photo->getClientOriginalName());
            assert($image instanceof Image);
            $image->imageMetadata['exif'] = $exif;
            CaptureMetadata::apply($image, $exif, 'Original EXIF GPS');
            $image->imageMetadata['cemeteryAssociation'] = ['method' => $input->cemeteryId !== null ? 'selected' : 'gps-radius', 'cemeteryId' => $cemetery->id];
            if ($corners !== null) $image->imageMetadata['correctionCorners'] = $corners;
            $em->persist($item);
            $em->flush();
            $bus->dispatch(new ProcessItem($item->id, correction: $correction));
            $response = $this->result($image, 202);
            $response->headers->set('Location', $this->generateUrl('photo_upload_status', ['id' => $image->id]));
            return $response;
        });
    }

    #[Route('/admin/photos/{id}/status', name: 'photo_upload_status', requirements: ['id' => '\d+'], methods: ['GET'], format: 'json')]
    public function status(Image $image): JsonResponse { return $this->result($image); }

    private function result(Image $image, int $status = 200): JsonResponse
    {
        return $this->json([
            'itemId' => $image->item->id, 'imageId' => $image->id,
            'status' => $image->item->marking, 'imageStatus' => $image->marking,
            'cemeteryId' => $image->item->cemetery?->id,
            'gps' => ['latitude' => $image->latitude, 'longitude' => $image->longitude, 'meaning' => 'camera position'],
            'needsCorners' => ($image->item->aiResults['processingPolicy']['correction'] ?? true) && !isset($image->imageMetadata['correctionCorners']),
            'url' => $this->generateUrl('item_show', ['id' => $image->item->id]),
            'correctionUrl' => $this->generateUrl('image_correction', ['id' => $image->id]),
            'statusUrl' => $this->generateUrl('photo_upload_status', ['id' => $image->id]),
        ], $status, ['Cache-Control' => 'private, no-store']);
    }
}
