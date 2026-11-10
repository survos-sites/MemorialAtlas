<?php

declare(strict_types=1);

namespace App\Workflow;

use App\Entity\{Media, Image};
use App\Service\ImageDecision;
use App\Decision\ImageQuestions as Q;
use Symfony\Component\Workflow\Attribute\AsGuardListener;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Attribute\AsTransitionListener;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\Workflow\Attribute\{AsWorkflow, Place, Transition};
use Symfony\Component\Workflow\WorkflowTrait;

#[AsWorkflow(name: 'media', supports: Media::class, markingProperty: 'marking')]
final class MediaWorkflow
{
    use WorkflowTrait;

    public function __construct(private readonly ImageDecision $decisions) {}

    #[Place(initial: true)]
    public const UPLOADED = 'uploaded';
    #[Transition(from: 'uploaded', to: 'processing')]
    public const PROCESS = 'process';
    #[Transition(from: 'processing', to: 'assessed')]
    #[Transition(from: 'assessed', to: 'assessed')]
    #[Transition(from: 'processed', to: 'assessed')]
    public const ASSESS = 'assess';
    #[Transition(from: 'processing', to: 'processed')]
    #[Transition(from: 'assessed', to: 'processed')]
    public const FINISH = 'finish';
    #[Transition(from: 'processing', to: 'failed')]
    #[Transition(from: 'assessed', to: 'failed')]
    public const FAIL = 'fail';
    #[Transition(from: 'failed', to: 'uploaded')]
    public const RETRY = 'retry';
    #[AsGuardListener(transition: self::FINISH)]
    public function requireAssessment(GuardEvent $event): void
    {
        $image = $event->getSubject();
        if ($image instanceof Image && (!isset($image->aiResults[Q::RESULT]) || $image->marking !== 'assessed')) {
            $event->setBlocked(true, 'An image must complete its assessment before finishing.');
        }
    }

    #[AsTransitionListener(transition: self::ASSESS)]
    public function assess(TransitionEvent $event): void
    {
        $image = $event->getSubject();
        if (!$image instanceof Image) throw new \LogicException('Image assessment requires an Image.');
        $this->decisions->assess($image);
    }
}

