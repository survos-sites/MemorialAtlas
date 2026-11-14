<?php

declare(strict_types=1);

namespace App\Search;

use App\Entity\{Image, Item};
use Survos\FieldBundle\Service\FieldReader;
use Survos\SearchBundle\Attribute\AsSearch;
use Survos\SearchBundle\Search\{AbstractFieldSearch, HitTemplateSearchInterface};

#[AsSearch(index: Item::class, name: 'items', adapter: 'es')]
final class ItemSearch extends AbstractFieldSearch implements HitTemplateSearchInterface
{
    public function __construct(private readonly FieldReader $fields, private readonly \App\Service\ImageDelivery $imagor) {}
    protected function getFieldClass(array $options = []): string { return ItemDocument::class; }
    public function getHitTemplate(): ?string { return 'search/item_hit.html.twig'; }

    public function document(Item $item): array
    {
        $document = get_object_vars(ItemDocument::fromItem($item));
        foreach ($item->media as $media) {
            if ($media instanceof Image) {
                $document['thumbnailUrl'] = $this->imagor->thumbnail($media);
                break;
            }
        }
        return $document;
    }

    public function build(array $options = []): void
    {
        $this->addEventListener(\Survos\SearchBundle\Event\PreSearchEvent::class, static function ($event): void {
            $event->getQuery()->hydrateEntities = false;
        });
        $this->addAvailableSort(null, 'Relevance');
        parent::build($options);
        // The bundle translates Doctrine entities; this public projection deliberately has no ORM mapping.
        $parameters = $this->getAdapterParameters();
        $mappings = $facets = $sorts = [];
        foreach ($this->fields->getDescriptors(ItemDocument::class) as $field) {
            $mappings[$field->name] = ['type' => $field->searchable ? 'text' : ($field->type === 'int' ? 'long' : 'keyword')];
            if ($field->searchable && ($field->facet || $field->sortable)) {
                $mappings[$field->name]['fields']['keyword'] = ['type' => 'keyword', 'ignore_above' => 512];
            }
            $exact = $field->name.($field->searchable ? '.keyword' : '');
            if ($field->facet) $facets[$field->name] = $exact;
            if ($field->sortable) $sorts[$field->name] = $exact;
        }
        $this->setAdapterParameters([
            'index' => 'items',
            'idField' => 'id',
            'searchFields' => array_map(static fn (string $field) => $field === 'names' ? 'names^3' : $field, $parameters['searchFields']),
            'sourceFields' => array_keys($mappings),
            'mappings' => $mappings,
            'facetFields' => $facets,
            'sortFields' => $sorts,
            'documentMapper' => $this->document(...),
        ]);
        $this->setAvailableHitsPerPage([12, 24, 48])->enableUrlRewriting();
    }
}
