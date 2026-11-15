<?php

declare(strict_types=1);

use App\Entity\{Image, Item};
use App\Platform\HuggingFaceAssistantMessageNormalizer;
use App\Service\{MemorialVectors, SymbolFinder};
use Symfony\AI\Platform\Contract;
use Symfony\AI\Platform\Message\Content\{Text, Thinking};
use Symfony\AI\Platform\Message\{AssistantMessage, Message, MessageBag};

require dirname(__DIR__).'/vendor/autoload.php';
$check = static function (bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); };

$item = new Item('Warzinski Family Gravestone');
$item->denseSummary = 'The WARZINSKI marker names THEODORE J., 1999 to 2018.';
$image = new Image($item, 0, 'w.jpg', 'aa/w', 'image/jpeg', hash('sha256', 'w'));
$image->aiResults[SymbolFinder::RESULT] = ['items' => [['name' => 'crossed lacrosse sticks', 'meaning' => 'played or loved lacrosse', 'category' => 'sport']]];

$text = MemorialVectors::text($item);
$check(str_starts_with($text, 'The WARZINSKI marker'), 'The embedded text starts with the dense summary.');
$check(str_contains($text, 'crossed lacrosse sticks (sport, a hobby or interest; played or loved lacrosse)'), 'Symbols carry their theme words, so a question about hobbies finds a carved sport.');

// The Hugging Face router rejects reasoning_content with a 400 on the second chat turn.
$contract = Contract::create([new HuggingFaceAssistantMessageNormalizer()]);
$bag = new MessageBag(Message::ofUser('Who has a baseball?'), new AssistantMessage(new Thinking('The user asks about baseball.'), new Text('Record 116.')), Message::ofUser('Tell me more.'));
$payload = $contract->createRequestPayload(new Symfony\AI\Platform\Model('Qwen/Qwen3.8-27B'), $bag);
$assistant = $payload['messages'][1];
$check($assistant['role'] === 'assistant' && $assistant['content'] === 'Record 116.', 'The answer text is sent.');
$check(!array_key_exists('reasoning_content', $assistant), 'The reasoning of an earlier turn is not sent to the router.');

echo "chat-memory: ok\n";
