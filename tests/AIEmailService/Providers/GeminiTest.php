<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Providers;

use Curl\Curl;
use HercegDoo\AIComposePlugin\AIEmailService\Debug\RequestLogger;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\EmailPrompt;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\Gemini;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class GeminiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!\defined('PHPUNIT_RUNNING')) {
            \define('PHPUNIT_RUNNING', true);
        }
        Settings::setDefaultMaxTokens(2000);
        Settings::setCreativity('medium');
    }

    public function testComposeMapsSharedPromptAndReturnsGeneratedText(): void
    {
        Settings::setProviderConfig(['apiKey' => 'secret-key', 'model' => 'gemini-3.8-flash']);
        $curl = $this->createMock(Curl::class);
        $curl->expects(self::exactly(2))->method('setHeader')->withConsecutive(
            ['Content-Type', 'application/json'],
            ['x-goog-api-key', 'secret-key']
        );
        $curl->expects(self::once())->method('setOpts')->with([
            \CURLOPT_TIMEOUT => 60,
            \CURLOPT_SSL_VERIFYPEER => true,
            \CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $curl->expects(self::once())->method('post')->with(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent',
            self::callback(static function (array $payload): bool {
                self::assertSame(['parts' => [['text' => 'Shared system instruction']]], $payload['systemInstruction']);
                self::assertSame([['role' => 'user', 'parts' => [['text' => 'Write an email']]]], $payload['contents']);
                self::assertSame(2000, $payload['generationConfig']['maxOutputTokens']);
                self::assertSame(['thinkingLevel' => 'low'], $payload['generationConfig']['thinkingConfig']);
                self::assertArrayNotHasKey('temperature', $payload['generationConfig']);

                return true;
            })
        )->willReturn((object) ['candidates' => [(object) [
            'content' => (object) ['parts' => [
                (object) ['text' => 'Hidden thought', 'thought' => true],
                (object) ['text' => '<p>Hello</p>'],
            ]],
            'finishReason' => 'STOP',
        ]]]);

        $request = RequestData::make('Recipient', 'Sender', 'Instruction', 'casual', 'medium', 'low', 'Bosnian');
        $result = (new Gemini($curl))->generateEmail($request, new EmailPrompt('Shared system instruction', 'Write an email'));

        self::assertSame('Gemini', (new Gemini($curl))->getProviderName());
        self::assertSame('<p>Hello</p>', $result->getBody());
    }

    public function testSummaryUsesItsOwnConfigAndLogsProviderTokenUsage(): void
    {
        $lines = [];
        $logger = new RequestLogger(true, '42', static function (string $line) use (&$lines): void {
            $lines[] = json_decode($line, true, 512, \JSON_THROW_ON_ERROR);
        });
        $curl = $this->createMock(Curl::class);
        $curl->httpStatusCode = 200;
        $curl->expects(self::once())->method('post')->with(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent',
            self::callback(static function (array $payload): bool {
                return $payload['generationConfig']['maxOutputTokens'] === 1200
                    && $payload['generationConfig']['thinkingConfig']['thinkingLevel'] === 'high'
                    && $payload['contents'][0]['parts'][0]['text'] === 'Summarize this message';
            })
        )->willReturn((object) [
            'responseId' => 'gemini-response-id',
            'candidates' => [(object) [
                'content' => (object) ['parts' => [(object) ['text' => '{"source_language":"German"}']]],
                'finishReason' => 'STOP',
            ]],
            'usageMetadata' => (object) [
                'promptTokenCount' => 40,
                'candidatesTokenCount' => 15,
                'thoughtsTokenCount' => 3,
                'totalTokenCount' => 58,
            ],
        ]);

        $result = (new Gemini($curl, $logger))->complete(
            new EmailPrompt('Summarize', 'Summarize this message', 'summary'),
            ['apiKey' => 'private-key', 'model' => 'gemini-3.8-flash', 'maxTokens' => 1200, 'thinkingLevel' => 'high']
        );

        self::assertSame('{"source_language":"German"}', $result);
        self::assertSame('summary', $lines[0]['operation']);
        self::assertSame($lines[0]['request_id'], $lines[1]['request_id']);
        self::assertSame(200, $lines[1]['details']['http_status']);
        self::assertSame('gemini-response-id', $lines[1]['details']['provider_request_id']);
        self::assertSame([
            'prompt_tokens' => 40,
            'completion_tokens' => 15,
            'total_tokens' => 58,
            'reasoning_tokens' => 3,
        ], $lines[1]['usage']);
        self::assertStringNotContainsString('private-key', json_encode($lines));
        self::assertStringNotContainsString('{"source_language":"German"}', json_encode($lines[1]));
    }

    public function testOlderModelUsesComposeCreativity(): void
    {
        Settings::setProviderConfig(['apiKey' => 'key', 'model' => 'gemini-2.5-flash']);
        $curl = $this->createMock(Curl::class);
        $curl->expects(self::once())->method('post')->with(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent',
            self::callback(static function (array $payload): bool {
                self::assertSame(0.8, $payload['generationConfig']['temperature']);
                self::assertArrayNotHasKey('thinkingConfig', $payload['generationConfig']);

                return true;
            })
        )->willReturn((object) ['candidates' => [(object) [
            'content' => (object) ['parts' => [(object) ['text' => 'Email']]],
        ]]]);

        $request = RequestData::make('Recipient', 'Sender', 'Instruction', 'casual', 'medium', 'high', 'Bosnian');
        self::assertSame('Email', (new Gemini($curl))->generateEmail($request, new EmailPrompt('System', 'User'))->getBody());
    }

    public function testRejectsMissingKeyAndUnsafeModelName(): void
    {
        $provider = new Gemini($this->createMock(Curl::class));
        foreach ([
            ['apiKey' => '', 'model' => 'gemini-3.8-flash'],
            ['apiKey' => 'key', 'model' => '../other-model'],
        ] as $config) {
            try {
                $provider->complete(new EmailPrompt('System', 'User'), $config);
                self::fail('Expected invalid Gemini configuration');
            } catch (ProviderException $e) {
                self::assertSame('Invalid Gemini configuration', $e->getMessage());
            }
        }
    }

    public function testReportsOutputTokenLimitWhenNoTextIsReturned(): void
    {
        $curl = $this->createMock(Curl::class);
        $curl->method('post')->willReturn((object) ['candidates' => [(object) [
            'content' => (object) ['parts' => []],
            'finishReason' => 'MAX_TOKENS',
        ]]]);

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Increase aiDefaultMaxTokens');
        (new Gemini($curl))->complete(new EmailPrompt('System', 'User'), ['apiKey' => 'key', 'model' => 'gemini-3.8-flash']);
    }

    public function testBlockedPromptDoesNotReturnContent(): void
    {
        $curl = $this->createMock(Curl::class);
        $curl->method('post')->willReturn((object) [
            'promptFeedback' => (object) ['blockReason' => 'SAFETY'],
        ]);

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Gemini prompt blocked');
        (new Gemini($curl))->complete(new EmailPrompt('System', 'User'), ['apiKey' => 'key', 'model' => 'gemini-3.8-flash']);
    }

    public function testApiFailureDoesNotExposeCredential(): void
    {
        $curl = $this->createMock(Curl::class);
        $curl->error = true;
        $curl->httpStatusCode = 403;
        $curl->errorMessage = 'Request with secret-key failed';
        $curl->method('post')->willReturn((object) []);

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Gemini API request failed');
        (new Gemini($curl))->complete(new EmailPrompt('System', 'User'), ['apiKey' => 'secret-key', 'model' => 'gemini-3.8-flash']);
    }
}
