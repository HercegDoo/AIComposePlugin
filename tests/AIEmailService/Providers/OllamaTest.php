<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Providers;

use Curl\Curl;
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
}
