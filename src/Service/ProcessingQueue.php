<?php
declare(strict_types=1);
namespace App\Service;

use App\Entity\Item;
use App\Message\ProcessItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class ProcessingQueue
{
    public function __construct(private EntityManagerInterface $em, private MessageBusInterface $bus, private ProcessingStatus $status) {}

    public function enqueue(Item $item): void
    {
        if (in_array($item->marking, ['review', 'complete'], true) || ($item->aiResults['processingQueued'] ?? false)) return;
        // The Doctrine transport uses the same connection: expose the marker and message together.
        $this->em->wrapInTransaction(function () use ($item): void {
            $item->aiResults['processingQueued'] = true;
            $this->em->flush();
            $this->bus->dispatch(new ProcessItem($item->id));
        });
    }

    #[AsEventListener]
    public function start(WorkerStartedEvent $event): void
    {
        if (!in_array('processing', $event->getWorker()->getMetadata()->getTransportNames(), true)) return;
        if ($this->status->pendingCount() === 0) return;
        $missing = $this->status->missingRequirements();
        if ($missing) throw new \RuntimeException('Configure .env.local before consuming: '.implode(', ', $missing).'. Queued photos have not been consumed.');
        foreach ($this->em->getRepository(Item::class)->findBy(['marking' => ['draft', 'ready', 'processing', 'failed']]) as $item) {
            $this->enqueue($item);
        }
    }
}
