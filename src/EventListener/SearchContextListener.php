<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\{Cemetery, Image, Item};
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

    public function __construct(private readonly ElasticSpooler $spooler) {}

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
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
        if (isset($this->pending[$key])) {
            $this->spooler->appendIds(Image::class, array_values($this->pending[$key]));
            unset($this->pending[$key]);
        }
    }

    public function onClear(OnClearEventArgs $args): void { unset($this->pending[spl_object_id($args->getObjectManager())]); }
    public function reset(): void { $this->pending = []; }
}
