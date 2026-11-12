<?php
declare(strict_types=1);
namespace App\MessageHandler;

use App\Entity\{Item, Image};
use App\Message\ProcessItem;
use App\Service\{ItemProcessor, ProcessingStatus};
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Survos\ElasticBundle\Service\ElasticIndexService;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;

#[AsMessageHandler]
final readonly class ProcessItemHandler
{
    public function __construct(private EntityManagerInterface $em, private ItemProcessor $processor, private ProcessingStatus $status, private ElasticIndexService $index, private LockFactory $locks, private LoggerInterface $logger) {}

    public function __invoke(ProcessItem $message): void
    {
        $lock = $this->locks->createLock('process-item-'.$message->itemId, 3600);
        if (!$lock->acquire()) throw new RecoverableMessageHandlingException('This record is being processed by another consumer.');
        try {
            $item = $this->em->find(Item::class, $message->itemId);
            if ($item === null) return;
            $this->em->refresh($item);
            if (!in_array($item->marking, ['review', 'complete'], true)) {
                $missing = $this->status->missingRequirements();
                if ($missing) throw new \RuntimeException('Missing processing configuration: '.implode(', ', $missing));
                $this->processor->process($item, fn (string $message) => $this->logger->info($message));
            }
            $this->index->indexIds(Item::class, [$item->id]);
            $ids = [];
            foreach ($item->media as $media) if ($media instanceof Image) $ids[] = $media->id;
            if ($ids) $this->index->indexIds(Image::class, $ids);
        } finally {
            $lock->release();
        }
    }
}
