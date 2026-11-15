<?php

declare(strict_types=1);

namespace App\Controller;

use App\Chat\{ChatQuestion, ToolCallLog};
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Agent\Execution\Update\Progress;
use Symfony\AI\Agent\MessageStore\MessageStoreInterface;
use Symfony\AI\Platform\Message\{Message, MessageBag};
use Symfony\AI\Platform\Result\Stream\Delta\TextDelta;
use Symfony\AI\Platform\Result\ToolCall;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\{RedirectResponse, Request, Response, StreamedResponse};
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Cache\{CacheInterface, ItemInterface};

/**
 * A chat over the collection. The agent uses the same tool methods as the MCP server. The agent's message
 * store keeps the conversation in Postgres; the session holds only its id, because a streamed response
 * starts after the session is saved. The page's turns (with the tool calls) are kept in the cache.
 */
final class ChatController extends AbstractController
{
    private const CONVERSATION = 'memorial_chat_id';

    public function __construct(
        #[Autowire(service: 'ai.agent.memorial_chat')] private AgentInterface $agent,
        #[Autowire(service: 'ai.message_store.doctrine.dbal.memorial_chat')] private MessageStoreInterface $messages,
        private ToolCallLog $log,
        private CacheInterface $cache,
        #[Autowire('%env(MEMORIAL_CHAT_MODEL)%')] private string $model,
    ) {}

    #[Route('/chat', name: 'chat', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->render('chat/index.html.twig', ['turns' => $this->turns($this->conversation($request))]);
    }

    /** Without JavaScript: the form posts here and the page reloads with the answer. */
    #[Route('/chat', name: 'chat_ask', methods: ['POST'])]
    public function ask(Request $request, #[MapRequestPayload] ChatQuestion $question): RedirectResponse
    {
        $id = $this->conversation($request);
        $this->answer($id, $question->question, static function (): void {});

        return $this->redirectToRoute('chat', ['_fragment' => 'turn-'.(count($this->turns($id)) - 1)]);
    }

    /** Server-sent events: "tool" for each tool call, "delta" for each piece of the answer, "done" with the rendered turn. */
    #[Route('/chat/stream', name: 'chat_stream', methods: ['POST'])]
    public function askStreamed(Request $request, #[MapRequestPayload] ChatQuestion $question): StreamedResponse
    {
        $id = $this->conversation($request);

        return new StreamedResponse(function () use ($id, $question): void {
            $send = static function (string $event, mixed $data): void {
                echo 'event: '.$event."\ndata: ".json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)."\n\n";
                @ob_flush();
                flush();
            };
            $turn = $this->answer($id, $question->question, $send);
            $send('done', ['html' => $this->renderView('chat/_turn.html.twig', ['turn' => $turn, 'index' => count($this->turns($id)) - 1])]);
        }, 200, ['Content-Type' => 'text/event-stream', 'Cache-Control' => 'no-cache', 'X-Accel-Buffering' => 'no']);
    }

    #[Route('/chat/reset', name: 'chat_reset', methods: ['POST'])]
    public function reset(Request $request): RedirectResponse
    {
        $request->getSession()->remove(self::CONVERSATION);

        return $this->redirectToRoute('chat');
    }

    /**
     * The same question after the same conversation gets the stored answer, with no model or tool calls;
     * it is still added to the stored conversation, so a follow-up question has the context.
     *
     * @param callable(string, mixed): void $send
     *
     * @return array{question: string, answer: string, tools: list<array<string, mixed>>}
     */
    private function answer(Uuid $id, string $question, callable $send): array
    {
        $turns = $this->turns($id);
        $key = 'chat_answer_'.hash('sha256', json_encode([$this->model, array_map(static fn (array $t): array => [$t['question'], $t['answer']], $turns),
            mb_strtolower(trim(preg_replace('/\s+/', ' ', $question)))], JSON_THROW_ON_ERROR));

        $fresh = false;
        try {
            $turn = $this->cache->get($key, function (ItemInterface $entry) use ($id, $question, $send, &$fresh): array {
                $entry->expiresAfter(86400);
                $fresh = true;
                $this->log->reset();
                $answer = '';
                $execution = $this->agent->call((new MessageBag(Message::ofUser($question)))->withId($id), ['stream' => true]);
                foreach ($execution as $update) {
                    if (!$update instanceof Progress) continue;
                    $payload = $update->getPayload();
                    if ($payload instanceof ToolCall) {
                        // Text before a tool call is the model narrating its plan, not the answer.
                        $answer = '';
                        $send('tool', ['name' => $payload->getName(), 'arguments' => $payload->getArguments()]);
                    }
                    if ($payload instanceof TextDelta) {
                        $answer .= $payload->getText();
                        $send('delta', $payload->getText());
                    }
                }

                return ['question' => $question, 'answer' => $answer, 'tools' => $this->log->calls()];
            });
            if (!$fresh) {
                $this->messages->save($this->messages->load($id)->merge(new MessageBag(Message::ofUser($turn['question']), Message::ofAssistant($turn['answer']))));
                $send('delta', $turn['answer']);
            }
        } catch (\Throwable $e) {
            $turn = ['question' => $question, 'answer' => 'The assistant could not answer: '.$e->getMessage(), 'tools' => $this->log->calls()];
            $send('delta', "\n\n".$turn['answer']);
        }

        $turns[] = $turn;
        $this->cache->delete('chat_turns_'.$id);
        $this->cache->get('chat_turns_'.$id, static function (ItemInterface $entry) use ($turns): array {
            $entry->expiresAfter(7 * 86400);

            return $turns;
        });

        return $turn;
    }

    private function conversation(Request $request): Uuid
    {
        $session = $request->getSession();
        if (!$session->has(self::CONVERSATION)) $session->set(self::CONVERSATION, Uuid::v7()->toRfc4122());

        return Uuid::fromString($session->get(self::CONVERSATION));
    }

    /** @return list<array{question: string, answer: string, tools: list<array<string, mixed>>}> */
    private function turns(Uuid $id): array
    {
        return $this->cache->get('chat_turns_'.$id, static fn (ItemInterface $entry): array => []);
    }
}
