<?php
declare(strict_types=1);
namespace App\Service;

use App\Entity\{Item, Image, Cemetery};
use AsyncAws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\{AsCommand, Argument};
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class DemoData
{
    public function __construct(private EntityManagerInterface $em, private MediaStorage $storage, private HttpClientInterface $http,
        #[Autowire('%env(S3_ENDPOINT)%')] private string $endpoint,
        #[Autowire('%env(AWS_S3_BUCKET_NAME)%')] private string $bucket,
        #[Autowire('%env(AWS_S3_ACCESS_ID)%')] private string $accessKey,
        #[Autowire('%env(AWS_S3_ACCESS_SECRET)%')] private string $secret,
        #[Autowire('%env(DEMO_MANIFEST_URL)%')] private string $manifestUrl,
        private ProcessingQueue $queue,
    ) {}

    private function client(): S3Client
    {
        return new S3Client(['endpoint'=>$this->endpoint, 'region'=>'fsn1', 'pathStyleEndpoint'=>true, 'accessKeyId'=>$this->accessKey, 'accessKeySecret'=>$this->secret]);
    }

    #[AsCommand('app:demo:publish', 'Publish one existing photo record and its recorded AI evidence as the hackathon demo')]
    public function publish(SymfonyStyle $io, #[Argument('Existing item ID')] int $id): int
    {
        $item = $this->em->find(Item::class, $id) ?? throw new \InvalidArgumentException('Item not found.');
        $pick = static function (object $entity, array $fields): array {
            $data=[]; foreach ($fields as $field) $data[$field]=$entity->$field; return $data;
        };
        $data=['version'=>1, 'key'=>'scanfony-demo-'.$id, 'item'=>$pick($item,['title','description','locationContext','recordType','marking','aiResults']),
            'cemetery'=>$item->cemetery ? $pick($item->cemetery,['name','locality']) : null, 'images'=>[]];
        foreach ($item->media as $media) {
            if (!$media instanceof Image) throw new \InvalidArgumentException('The quick-start demo supports photo records.');
            $image=$pick($media,['id','originalName','mimeType','sha256','source','donor','description','ocrText','latitude','longitude','marking','aiResults','imageMetadata']);
            $image['objectKey']='scanfony/demo/originals/'.$media->sha256;
            $stream=fopen($this->storage->path($media),'rb');
            try {$this->client()->putObject(['Bucket'=>$this->bucket,'Key'=>$image['objectKey'],'Body'=>$stream,'ContentType'=>$media->mimeType])->resolve();}
            finally {fclose($stream);}
            $data['images'][]=$image;
        }
        $this->client()->putObject(['Bucket'=>$this->bucket,'Key'=>'scanfony/demo/v1.json','Body'=>json_encode($data,JSON_THROW_ON_ERROR),'ContentType'=>'application/json'])->resolve();
        $io->success('Published the demo and recorded evidence to the configured S3 bucket.');
        return 0;
    }

    public function load(): Item
    {
        $data=$this->http->request('GET', $this->manifestUrl)->toArray();
        if (($data['version'] ?? null) === 2) {
            if (empty($data['records'])) throw new \RuntimeException('Empty demo collection.');
            $first = null;
            $loaded = [];
            foreach ($data['records'] as $record) {
                $item = $this->loadRecord($record);
                $loaded[$record['key']] = $item;
                $first ??= $item;
            }
            $this->linkSameMarkers($loaded);
            return $first;
        }
        return $this->loadRecord($data);
    }

    /**
     * Writes every processed record with its AI results as a version 2 package, so another installation
     * loads the results without calling any model. Same-marker links are written as package keys.
     */
    #[AsCommand('app:demo:export', 'Write all processed records and their AI results as a version 2 demo package')]
    public function export(SymfonyStyle $io, #[Argument('Output file')] string $path): int
    {
        $items = $this->em->getRepository(Item::class)->findBy(['marking' => ['review', 'complete']], ['id' => 'ASC']);
        $keys = [];
        foreach ($items as $item) $keys[$item->id] = $item->aiResults['demoKey'] ?? 'memorialatlas-'.$item->id;
        $records = [];
        foreach ($items as $item) {
            $ai = $item->aiResults;
            unset($ai['demoKey'], $ai['processingQueued']);
            if (isset($ai['sameMarker'])) $ai['sameMarker'] = ['protocol' => $ai['sameMarker']['protocol'] ?? null,
                'sameAsKeys' => array_values(array_map(static fn (int $id): string => $keys[$id], array_filter($ai['sameMarker']['sameAs'] ?? [], static fn (int $id): bool => isset($keys[$id]))))];
            $images = [];
            foreach ($item->media as $media) {
                if (!$media instanceof Image || $media->sourceUrl === null) continue;
                $images[] = ['sha256' => $media->sha256, 'url' => $media->sourceUrl, 'imageUrl' => $media->imageUrl, 'originalName' => $media->originalName,
                    'mimeType' => $media->mimeType, 'source' => $media->source, 'donor' => $media->donor, 'description' => $media->description,
                    'ocrText' => $media->ocrText, 'latitude' => $media->latitude, 'longitude' => $media->longitude, 'marking' => $media->marking,
                    'aiResults' => $media->aiResults, 'imageMetadata' => $media->imageMetadata];
            }
            $records[] = ['version' => 1, 'key' => $keys[$item->id],
                'cemetery' => $item->cemetery === null ? null : ['name' => $item->cemetery->name, 'locality' => $item->cemetery->locality],
                'item' => ['title' => $item->title, 'description' => $item->description, 'locationContext' => $item->locationContext,
                    'recordType' => $item->recordType, 'marking' => $item->marking, 'aiResults' => $ai, 'tags' => $item->tags],
                'images' => $images];
        }
        file_put_contents($path, json_encode(['version' => 2, 'records' => $records], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $io->success(sprintf('%d records written to %s (%.1f MB).', count($records), $path, filesize($path) / 1e6));
        return 0;
    }

    /** @param array<string, Item> $loaded */
    private function linkSameMarkers(array $loaded): void
    {
        foreach ($loaded as $item) {
            $keys = $item->aiResults['sameMarker']['sameAsKeys'] ?? null;
            if ($keys !== null) $item->aiResults['sameMarker']['sameAs'] = array_values(array_map(static fn (string $k): int => $loaded[$k]->id, array_filter($keys, static fn (string $k): bool => isset($loaded[$k]))));
            $item->denseSummary = Remembrance::denseSummary($item);
        }
        $this->em->flush();
    }

    private function loadRecord(array $data): Item
    {
        if (($data['version']??null)!==1 || empty($data['images'])) throw new \RuntimeException('Unsupported or empty demo package.');
        // A durable demo identity makes retries idempotent without overwriting reviewed records.
        $existing=$this->em->getConnection()->fetchOne("SELECT id FROM item WHERE ai_results->>'demoKey' = ?",[$data['key']]);
        if ($existing) {
            $item = $this->em->find(Item::class,(int)$existing);
            $this->queue->enqueue($item);
            return $item;
        }
        $data['item'] = ($data['item'] ?? []) + ['title'=>'Unprocessed gravestone', 'description'=>null, 'locationContext'=>null, 'recordType'=>'gravestone', 'marking'=>'draft', 'aiResults'=>[]];
        $item=new Item($data['item']['title']);
        foreach (['description','locationContext','recordType','marking','aiResults'] as $field) $item->$field=$data['item'][$field];
        $item->tags = $data['item']['tags'] ?? [];
        $item->aiResults['demoKey']=$data['key'];
        if ($data['cemetery'] ?? null) {
            $cemetery=$this->em->getRepository(Cemetery::class)->findOneBy($data['cemetery']) ?? new Cemetery();
            $cemetery->name=$data['cemetery']['name']; $cemetery->locality=$data['cemetery']['locality'];
            $this->em->persist($cemetery); $item->cemetery=$cemetery;
        }
        foreach ($data['images'] as $image) {
            $image += ['originalName'=>($image['sha256'] ?? 'photo').'.jpg', 'mimeType'=>'image/jpeg', 'source'=>'Public dataset', 'donor'=>null, 'description'=>null, 'ocrText'=>null, 'latitude'=>null, 'longitude'=>null, 'marking'=>'uploaded', 'aiResults'=>[], 'imageMetadata'=>[]];
            if (!preg_match('/^[a-f0-9]{64}$/D',$image['sha256']) || !str_starts_with($image['url'] ?? '', 'https://')) throw new \RuntimeException('Invalid demo image reference.');
            $media=new Image($item, count($item->media), $image['originalName'], substr($image['sha256'],0,2).'/'.$image['sha256'], $image['mimeType'], $image['sha256']);
            $media->sourceUrl=$image['url'];
            if (isset($image['imageUrl'])) $media->imageUrl=$image['imageUrl'];
            foreach (['source','donor','description','ocrText','latitude','longitude','marking','aiResults','imageMetadata'] as $field) $media->$field=$image[$field];
            // Retain provider receipts as originally recorded; source IDs are provenance, not local foreign keys.
            if (isset($image['id'])) $media->aiResults['demoSourceMediaId']=$image['id'];
        }
        $this->em->persist($item); $this->em->flush();
        $this->queue->enqueue($item);
        return $item;
    }
}
