<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\{Cemetery, Image, Item, Media};
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\{OnFlushEventArgs, PostFlushEventArgs, OnClearEventArgs};
use Doctrine\ORM\Events;
use Survos\ElasticBundle\Spool\ElasticSpooler;
use Symfony\Contracts\Service\ResetInterface;

#[AsDoctrineListener(Events::onFlush)]
#[AsDoctrineListener(Events::postFlush)]
#[AsDoctrineListener(Events::onClear)]
final class SearchContextListener implements ResetInterface
{
    private array $pending = [];
    private array $pendingItems = [];

    public function __construct(private readonly ElasticSpooler $spooler) {}

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        $key = spl_object_id($em);
        foreach ([...$uow->getScheduledEntityInsertions(), ...$uow->getScheduledEntityUpdates(), ...$uow->getScheduledEntityDeletions()] as $entity) {
            if ($entity instanceof Media) {
                $this->pendingItems[$key][spl_object_id($entity->item)] = $entity->item;
                $previous = $uow->getEntityChangeSet($entity)['item'][0] ?? null;
                if ($previous instanceof Item) $this->pendingItems[$key][spl_object_id($previous)] = $previous;
            }
            if ($entity instanceof Cemetery) {
                foreach ($em->getRepository(Item::class)->findBy(['cemetery' => $entity]) as $item) {
                    $this->pendingItems[$key][spl_object_id($item)] = $item;
                }
            }
        }
        foreach ([...$uow->getScheduledEntityUpdates(), ...$uow->getScheduledEntityDeletions()] as $entity) {
            if (!$entity instanceof Item && !$entity instanceof Cemetery) continue;
            $qb = $em->createQueryBuilder()->select('i.id')->from(Image::class, 'i');
            if ($entity instanceof Item) $qb->where('i.item = :context');
            else $qb->join('i.item', 'record')->where('record.cemetery = :context');
            foreach ($qb->setParameter('context', $entity)->getQuery()->getSingleColumnResult() as $id) {
                $this->pending[spl_object_id($em)][$id] = $id;
            }
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        $key = spl_object_id($args->getObjectManager());
        if (isset($this->pendingItems[$key])) {
            $ids = array_values(array_filter(array_map(static fn (Item $item) => $item->id, $this->pendingItems[$key])));
            if ($ids !== []) $this->spooler->appendIds(Item::class, $ids);
            unset($this->pendingItems[$key]);
        }
        if (isset($this->pending[$key])) {
            $this->spooler->appendIds(Image::class, array_values($this->pending[$key]));
            unset($this->pending[$key]);
        }
    }

    public function onClear(OnClearEventArgs $args): void { unset($this->pending[spl_object_id($args->getObjectManager())], $this->pendingItems[spl_object_id($args->getObjectManager())]); }
    public function reset(): void { $this->pending = []; $this->pendingItems = []; }
}
