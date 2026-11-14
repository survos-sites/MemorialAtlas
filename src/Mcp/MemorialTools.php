<?php

declare(strict_types=1);

namespace App\Mcp;

use App\Decision\ImageQuestions as Q;
use App\Entity\{Image, Item};
use App\Image\ImagePreset;
use App\Service\{ImageCleaner, ImageDelivery};
use Doctrine\ORM\EntityManagerInterface;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Schema\ToolAnnotations;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Read-only MCP tools over the memorial collection. Every answer separates what the AI proposed from
 * what is verified: nothing here is a verified burial record, and AI-cleaned photos are display copies.
 */
final readonly class MemorialTools
{
    public function __construct(
        private EntityManagerInterface $em,
        private ImageDelivery $images,
        private UrlGeneratorInterface $urls,
    ) {}

    /**
     * Search memorial records by name, inscription text or description.
     *
     * @param string $query Words to look for, for example a surname or a year
     * @param int    $limit Maximum number of records (1 to 50)
     *
     * @return list<array<string, mixed>>
     */
    #[McpTool(name: 'search_memorials', annotations: new ToolAnnotations(title: 'Search memorials', readOnlyHint: true, openWorldHint: false))]
    public function search(string $query, int $limit = 10): array
    {
        $ids = $this->em->getConnection()->fetchFirstColumn(
            'SELECT i.id FROM item i LEFT JOIN media m ON m.item_id = i.id
             WHERE i.title ILIKE :q OR i.description ILIKE :q OR m.ocr_text ILIKE :q
             GROUP BY i.id ORDER BY i.id LIMIT :limit',
            ['q' => '%'.addcslashes(trim($query), '%_\\').'%', 'limit' => max(1, min(50, $limit))],
        );

        return array_map(fn ($id) => $this->summary($this->em->find(Item::class, $id)), $ids);
    }

    /**
     * Get one memorial record with its photos, OCR text, proposed people and open questions.
     *
     * @param int $id Record id, as returned by search_memorials
     *
     * @return array<string, mixed>
     */
    #[McpTool(name: 'get_memorial', annotations: new ToolAnnotations(title: 'Get a memorial record', readOnlyHint: true, openWorldHint: false))]
    public function get(int $id): array
    {
        $item = $this->em->find(Item::class, $id) ?? throw new \Mcp\Exception\ToolCallException('No memorial record with id '.$id.'. Use search_memorials to find ids.');
        $record = $item->aiResults['catalog']['record'] ?? [];

        return $this->summary($item) + [
            'description' => $item->description,
            'locationContext' => $item->locationContext,
            'proposedPeople' => $record['people'] ?? [],
            'uncertainties' => $record['uncertainties'] ?? [],
            'extractedBy' => isset($item->aiResults['catalog']) ? ($item->aiResults['catalog']['provider'] ?? null).' '.($item->aiResults['catalog']['model'] ?? null) : null,
            'note' => 'AI proposals for human review. Not a verified burial record.',
            'photos' => array_values(array_map(fn (Image $image) => $this->photo($image), array_filter($item->media->toArray(), static fn ($m) => $m instanceof Image))),
        ];
    }

    /**
     * List processed records that wait for a person to check them, with the AI's open questions.
     *
     * @param int $limit Maximum number of records (1 to 50)
     *
     * @return list<array<string, mixed>>
     */
    #[McpTool(name: 'list_records_to_review', annotations: new ToolAnnotations(title: 'Records to review', readOnlyHint: true, openWorldHint: false))]
    public function toReview(int $limit = 20): array
    {
        $items = $this->em->getRepository(Item::class)->findBy(['marking' => 'review'], ['id' => 'ASC'], max(1, min(50, $limit)));

        return array_map(fn (Item $item) => $this->summary($item) + ['uncertainties' => $item->aiResults['catalog']['record']['uncertainties'] ?? []], $items);
    }

    /**
     * Count records by processing status and photos by AI treatment, per cemetery.
     *
     * @return array<string, mixed>
     */
    #[McpTool(name: 'collection_overview', annotations: new ToolAnnotations(title: 'Collection overview', readOnlyHint: true, openWorldHint: false))]
    public function overview(): array
    {
        $db = $this->em->getConnection();

        return [
            'cemeteries' => $db->fetchAllAssociative('SELECT c.name, c.locality, COUNT(i.id) AS records FROM cemetery c LEFT JOIN item i ON i.cemetery_id = c.id GROUP BY c.id ORDER BY c.name'),
            'recordsByStatus' => $db->fetchAllKeyValue('SELECT marking, COUNT(*) FROM item GROUP BY marking ORDER BY marking'),
            'photos' => (int) $db->fetchOne('SELECT COUNT(*) FROM media'),
            'photosWithOcr' => (int) $db->fetchOne('SELECT COUNT(*) FROM media WHERE ocr_text IS NOT NULL'),
            'aiCleanedViews' => (int) $db->fetchOne("SELECT COUNT(*) FROM media WHERE image_metadata::jsonb -> 'cleanedView' IS NOT NULL"),
        ];
    }

    /** @return array<string, mixed> */
    private function summary(Item $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'cemetery' => $item->cemetery === null ? null : trim($item->cemetery->name.', '.$item->cemetery->locality, ', '),
            'status' => $item->marking,
            'url' => $this->urls->generate('item_show', ['id' => $item->id], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    /** @return array<string, mixed> */
    private function photo(Image $image): array
    {
        $cleaned = $image->imageMetadata[ImageCleaner::KEY] ?? null;
        $decision = $image->aiResults[Q::RESULT] ?? null;

        return [
            'id' => $image->id,
            'originalName' => $image->originalName,
            'cameraGps' => $image->latitude === null ? null : ['latitude' => $image->latitude, 'longitude' => $image->longitude],
            'originalUrl' => $image->imageUrl === null ? null : $this->images->detail($image),
            'ocrText' => $image->ocrText,
            'jevRoute' => $decision['route'] ?? null,
            'aiCleanedView' => $cleaned === null ? null : [
                'url' => $this->images->cleaned($image, ImagePreset::Detail),
                'model' => $cleaned['model'],
                'note' => 'Straightened and relit by an image model for display. It can change letters; the original photo is the evidence.',
            ],
        ];
    }
}
