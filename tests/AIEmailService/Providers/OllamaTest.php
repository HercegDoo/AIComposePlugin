<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Providers;

use Curl\Curl;
use HercegDoo\AIComposePlugin\AIEmailService\Debug\RequestLogger;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\EmailPrompt;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\Ollama;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class OllamaTest extends TestCase
{
    public function testSendsSharedPromptToLocalChatApi(): void
    {
        $curl = $this->createMock(Curl::class);
        $curl->expects(self::once())->method('post')->with(
            'http://127.0.0.1:11434/api/chat',
            self::callback(static function (array $payload): bool {
                return $payload['model'] === 'multilingual-model'
                    && $payload['format'] === 'json'
                    && $payload['stream'] === false
                    && $payload['messages'][0]['content'] === 'System summary instruction'
                    && $payload['messages'][1]['content'] === 'User email content';
            })
        )->willReturn((object) ['message' => (object) ['content' => '{"source_language":"German"}']]);

        $response = (new Ollama($curl))->complete(
            new EmailPrompt('System summary instruction', 'User email content'),
            ['model' => 'multilingual-model']
        );

        self::assertSame('{"source_language":"German"}', $response);
    }

    public function testRejectsMissingModel(): void
    {
        $this->expectException(ProviderException::class);
        (new Ollama())->complete(new EmailPrompt('System', 'User'), ['model' => '']);
    }

    public function testDebugLogRecordsOllamaTokenCounts(): void
    {
        $lines = [];
        $logger = new RequestLogger(true, '42', static function (string $line) use (&$lines): void {
            $lines[] = json_decode($line, true, 512, \JSON_THROW_ON_ERROR);
        });
        $curl = $this->createMock(Curl::class);
        $curl->method('post')->willReturn((object) [
            'message' => (object) ['content' => 'Private summary'],
            'prompt_eval_count' => 40,
            'eval_count' => 15,
            'done_reason' => 'stop',
        ]);

        $result = (new Ollama($curl, $logger))->complete(
            new EmailPrompt('System summary', 'Incoming email', 'summary'),
            ['model' => 'test-model']
        );

        self::assertSame('Private summary', $result);
        self::assertSame('summary', $lines[0]['operation']);
        self::assertSame(['prompt_tokens' => 40, 'completion_tokens' => 15, 'total_tokens' => 55], $lines[1]['usage']);
        self::assertStringNotContainsString('Private summary', json_encode($lines));
    }
}
