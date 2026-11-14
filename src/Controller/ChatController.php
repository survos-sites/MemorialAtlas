<?php

declare(strict_types=1);

namespace App\Controller;

use App\Chat\{ChatQuestion, ToolCallLog};
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\{Message, MessageBag};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\{RedirectResponse, Request, Response};
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

/** A chat over the collection. The agent uses the same tool methods as the MCP server. */
final class ChatController extends AbstractController
{
    private const HISTORY = 'memorial_chat';
    /** Keeps the prompt small: older turns drop out of the conversation the model sees. */
    private const KEEP_TURNS = 6;

    public function __construct(
        #[Autowire(service: 'ai.agent.memorial_chat')] private AgentInterface $agent,
        private ToolCallLog $log,
    ) {}

    #[Route('/chat', name: 'chat', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->render('chat/index.html.twig', ['turns' => $request->getSession()->get(self::HISTORY, [])]);
    }

    #[Route('/chat', name: 'chat_ask', methods: ['POST'])]
    public function ask(Request $request, #[MapRequestPayload] ChatQuestion $question): RedirectResponse
    {
        $session = $request->getSession();
        $turns = $session->get(self::HISTORY, []);

        $history = [];
        foreach (array_slice($turns, -self::KEEP_TURNS) as $turn) {
            $history[] = Message::ofUser($turn['question']);
            $history[] = Message::ofAssistant($turn['answer']);
        }
        $messages = new MessageBag(...$history, ...[Message::ofUser($question->question)]);

        $this->log->reset();
        try {
            $answer = (string) $this->agent->call($messages)->getContent();
        } catch (\Throwable $e) {
            $answer = 'The assistant could not answer: '.$e->getMessage();
        }
        $turns[] = ['question' => $question->question, 'answer' => $answer, 'tools' => $this->log->calls()];
        $session->set(self::HISTORY, $turns);

        return $this->redirectToRoute('chat', ['_fragment' => 'turn-'.(count($turns) - 1)]);
    }

    #[Route('/chat/reset', name: 'chat_reset', methods: ['POST'])]
    public function reset(Request $request): RedirectResponse
    {
        $request->getSession()->remove(self::HISTORY);

        return $this->redirectToRoute('chat');
    }
}
