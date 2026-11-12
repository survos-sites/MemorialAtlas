<?php
declare(strict_types=1);
namespace App\Service;

use App\Entity\{Item, Image, Cemetery};
use AsyncAws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\{AsCommand, Argument};
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

final readonly class DemoData
{
    public function __construct(private EntityManagerInterface $em, private MediaStorage $storage,
        #[Autowire('%env(S3_ENDPOINT)%')] private string $endpoint,
        #[Autowire('%env(AWS_S3_BUCKET_NAME)%')] private string $bucket,
        #[Autowire('%env(AWS_S3_ACCESS_ID)%')] private string $accessKey,
        #[Autowire('%env(AWS_S3_ACCESS_SECRET)%')] private string $secret,
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
        $data=json_decode($this->client()->getObject(['Bucket'=>$this->bucket,'Key'=>'scanfony/demo/v1.json'])->getBody()->getContentAsString(),true,512,JSON_THROW_ON_ERROR);
        if (($data['version']??null)!==1 || empty($data['images'])) throw new \RuntimeException('Unsupported or empty demo package.');
        // A durable demo identity makes retries idempotent without overwriting reviewed records.
        $existing=$this->em->getConnection()->fetchOne("SELECT id FROM item WHERE ai_results->>'demoKey' = ?",[$data['key']]);
        if ($existing) return $this->em->find(Item::class,(int)$existing);
        $files=new Filesystem();
        $item=new Item($data['item']['title']);
        foreach (['description','locationContext','recordType','marking','aiResults'] as $field) $item->$field=$data['item'][$field];
        $item->aiResults['demoKey']=$data['key'];
        if ($data['cemetery']) {
            $cemetery=$this->em->getRepository(Cemetery::class)->findOneBy($data['cemetery']) ?? new Cemetery();
            $cemetery->name=$data['cemetery']['name']; $cemetery->locality=$data['cemetery']['locality'];
            $this->em->persist($cemetery); $item->cemetery=$cemetery;
        }
        foreach ($data['images'] as $image) {
            if (!preg_match('/^[a-f0-9]{64}$/D',$image['sha256']) || $image['objectKey']!=='scanfony/demo/originals/'.$image['sha256']) throw new \RuntimeException('Invalid demo image reference.');
            $tmp=$files->tempnam(sys_get_temp_dir(),'scanfony-demo-');
            try {
                $files->dumpFile($tmp, $this->client()->getObject(['Bucket'=>$this->bucket,'Key'=>$image['objectKey']])->getBody()->getContentAsString());
                if (hash_file('sha256',$tmp)!==$image['sha256']) throw new \RuntimeException('Demo image checksum mismatch.');
                $media=$this->storage->import($item,$tmp,$image['originalName']);
            } finally {$files->remove($tmp);}
            foreach (['source','donor','description','ocrText','latitude','longitude','marking','aiResults','imageMetadata'] as $field) $media->$field=$image[$field];
            // Retain provider receipts as originally recorded; source IDs are provenance, not local foreign keys.
            $media->aiResults['demoSourceMediaId']=$image['id'];
        }
        $this->em->persist($item); $this->em->flush();
        return $item;
    }
}
