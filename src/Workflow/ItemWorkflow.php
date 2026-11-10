<?php

declare(strict_types=1);

namespace App\Workflow;

use App\Entity\Item;
use Symfony\Component\Workflow\Attribute\{AsWorkflow, AsGuardListener, Place, Transition};
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\WorkflowTrait;

#[AsWorkflow(name: 'item', supports: Item::class, markingProperty: 'marking')]
final class ItemWorkflow
{
    use WorkflowTrait;

    #[Place(initial: true)]
    public const DRAFT = 'draft';
    #[Transition(from: 'draft', to: 'ready')]
    public const SUBMIT = 'submit';
    #[Transition(from: 'ready', to: 'processing')]
    public const PROCESS = 'process';
    #[Transition(from: 'processing', to: 'review')]
    public const PROPOSE = 'propose';
    #[Transition(from: 'review', to: 'complete')]
    public const APPROVE = 'approve';
    #[Transition(from: 'processing', to: 'failed')]
    public const FAIL = 'fail';
    #[Transition(from: 'failed', to: 'ready')]
    public const RETRY = 'retry';
    #[Transition(from: 'review', to: 'draft')]
    public const REVISE = 'revise';

    #[AsGuardListener(transition: self::SUBMIT)]
    public function requireMedia(GuardEvent $event): void
    {
        if ($event->getSubject()->media->isEmpty()) {
            $event->setBlocked(true, 'Add at least one photo or recording before submitting.');
        }
    }
}
