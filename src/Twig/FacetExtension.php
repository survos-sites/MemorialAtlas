<?php

declare(strict_types=1);
namespace App\Twig;

use Survos\SearchBundle\Context\ContextProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** Public discovery hides unavailable assessments without changing the indexed evidence. */
final class FacetExtension extends AbstractExtension
{
    public function __construct(private readonly ContextProvider $contextProvider) {}

    public function getFunctions(): array
    {
        return [new TwigFunction('memorial_facet_visible', $this->visible(...))];
    }

    public function visible(string $property): bool
    {
        $context = $this->contextProvider->getCurrentContext();
        if ($context->getQuery()->hasActiveFilter($property)) return true;
        $distributions = $context->getResults()?->getFacetDistributions() ?? [];
        // Range widgets use statistics, not term distributions, and retain their bundle behavior.
        if (!isset($distributions[$property])) return true;
        $distribution = $distributions[$property];
        if ($distribution->getCheckedValues() !== []) return true;
        foreach ($distribution->getValues() as $value => $count) {
            if ($count > 0 && trim((string) $value) !== '' && $value !== 'Not assessed') return true;
        }
        return false;
    }
}
