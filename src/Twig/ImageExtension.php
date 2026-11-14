<?php

declare(strict_types=1);

namespace App\Twig;

use App\Service\ImageDelivery;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ImageExtension extends AbstractExtension
{
    public function __construct(private readonly ImageDelivery $images, private readonly \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router) {}

    public function correction(\App\Entity\Image $image, array $correction, string $frame): string
    {
        $source = $correction[$frame === 'original' ? 'parentFrame' : 'frame']['source'];
        return $this->images->storedDetail($source, $this->router->generate('image_correction_file', ['id' => $image->id, 'correctionId' => $correction['id'], 'frame' => $frame], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL));
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('memorial_correction', $this->correction(...)),
            new TwigFunction('memorial_thumbnail', $this->images->thumbnail(...)),
            new TwigFunction('memorial_detail', $this->images->detail(...)),
            new TwigFunction('memorial_image_origin', $this->images->origin(...)),
        ];
    }
}
