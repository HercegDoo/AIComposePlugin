<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Providers;

use Curl\Curl;
use DG\BypassFinals;
use HercegDoo\AIComposePlugin\AIEmailService\Debug\RequestLogger;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\EmailPrompt;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\OpenAI;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use HercegDoo\AIComposePlugin\TestSupport\ReflectionHelper;
use PHPUnit\Framework\TestCase;

BypassFinals::enable();

/**
 * @internal
 *
 * @coversNothing
 */
final class OpenAITest extends TestCase
{
    protected RequestData $requestData;
    private EmailPrompt $prompt;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PHPUNIT_RUNNING')) {
            \define('PHPUNIT_RUNNING', true);
        }

        Settings::setStyles(['professional', 'default' => 'casual', 'assertive', 'enthusiastic', 'funny', 'informational', 'persuasive']);

        Settings::setLengths(['short', 'default' => 'medium', 'long']);

        Settings::setLanguages(['default' => 'Bosnian', 'Croatian', 'German', 'Dutch']);

        Settings::setDefaultMaxTokens(2000);
        Settings::setProviderConfig(['apiKey' => 'test-api-key', 'model' => 'model-test']);

        $this->requestData = RequestData::make('Meho', 'Muhi', 'TestInstrukcija');
        $this->requestData->setSignaturePresent(false);
        $this->requestData->setMultipleRecipients(false);
        $this->prompt = new EmailPrompt('System instruction', 'Email instruction');
    }

    public function testSetError()
    {
        $OpenAi = new OpenAI();
        self::assertNull($OpenAi->getErrors()[\count($OpenAi->getErrors()) - 1] ?? null);
        $OpenAi->setError('MyError');
        self::assertSame('MyError', $OpenAi->getErrors()[\count($OpenAi->getErrors()) - 1]);
        $OpenAi->setError('MyError1');
        self::assertSame('MyError1', $OpenAi->getErrors()[\count($OpenAi->getErrors()) - 1]);
        self::assertNull($OpenAi->setError('MyError'));
    }

    public function testGetError()
    {
        $OpenAi = new OpenAI();
        self::assertIsArray($OpenAi->getErrors());
        self::assertCount(0, $OpenAi->getErrors());

        $OpenAi->setError('MyError');
        self::assertCount(1, $OpenAi->getErrors());
    }

    public function testHasError()
    {
        $OpenAi = new OpenAI();
        self::assertFalse($OpenAi->hasErrors());
        $OpenAi->setError('MyError');
        self::assertTrue($OpenAi->hasErrors());
    }

    public function testGetProviderName()
    {
        $OpenAi = new OpenAI();
        self::assertSame('OpenAI', $OpenAi->getProviderName());
    }

    public function testGenerateEmailAssignPropertiesDefault()
    {
        $mockCurl = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $openAI = new OpenAI($mockCurl);

        Settings::setProviderConfig(['apiKey' => 'test-api-key', 'model' => 'test-model']);

        try {
            $openAI->generateEmail($this->requestData, $this->prompt);
        } catch (ProviderException $exception) {
            self::assertSame('test-api-key', ReflectionHelper::getPrivateProperty($openAI, 'apiKey'));
            self::assertSame('test-model', ReflectionHelper::getPrivateProperty($openAI, 'model'));
            self::assertSame(2000, ReflectionHelper::getPrivateProperty($openAI, 'maxTokens'));
            self::assertSame(0.5, ReflectionHelper::getPrivateProperty($openAI, 'creativity'));
        }
    }

    public function testGenerateEmailErrorException()
    {
        $mockCurl = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        Settings::setProviderConfig(['apiKey' => 'test-api-key', 'model' => 'test-model']);

        $openAI = new OpenAI($mockCurl);

        $openAI->setError('dummyError');

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('dummyError');
        $openAI->generateEmail($this->requestData, $this->prompt);
    }

    public function testGenerateEmailReturnType()
    {
        $this->requestData = RequestData::make('Meho', 'Muhi', 'jabuka', 'casual', 'medium', 'low', 'Bosnian');
        $this->requestData->setInstruction('afdsafsd');
        $this->requestData->setMultipleRecipients(false);
        $this->requestData->setSignaturePresent(false);

        $curlMock = $this->createMock(Curl::class);
        $mockResponse = new \stdClass();
        $mockResponse->choices = [
            (object) [
                'message' => (object) [
                    'role' => 'assistant',
                    'content' => "\n\nThis is a test!",
                ],
                'logprobs' => null,
                'finish_reason' => 'stop',
                'index' => 0,
            ],
        ];

        $curlMock->method('post')->willReturn($mockResponse);
        $OpenAI = new OpenAI($curlMock);

        $return = $OpenAI->generateEmail($this->requestData, $this->prompt);

        self::assertInstanceOf(Respond::class, $return);
    }

    public function testSummaryCompletionUsesItsOwnConfiguration(): void
    {
        Settings::setProviderConfig(['apiKey' => 'compose-key', 'model' => 'gpt-5']);
        $curl = $this->createMock(Curl::class);
        $curl->expects(self::once())->method('post')->with(
            self::equalTo('https://api.openai.com/v1/chat/completions'),
            self::callback(static function (array $payload): bool {
                return $payload['model'] === 'gpt-4.1'
                    && $payload['max_tokens'] === 1200
                    && $payload['temperature'] === 0.0
                    && $payload['messages'][1]['content'] === 'Summarize this message';
            })
        )->willReturn((object) ['choices' => [(object) ['message' => (object) ['content' => 'Summary']]]]);

        $result = (new OpenAI($curl))->complete(
            new EmailPrompt('Summary system prompt', 'Summarize this message'),
            ['apiKey' => 'summary-key', 'model' => 'gpt-4.1', 'maxTokens' => 1200, 'temperature' => 0]
        );

        self::assertSame('Summary', $result);
    }

    public function testDebugLogIncludesProviderUsageWithoutApiKeyOrResponseBody(): void
    {
        $lines = [];
        $logger = new RequestLogger(true, '42', static function (string $line) use (&$lines): void {
            $lines[] = json_decode($line, true, 512, \JSON_THROW_ON_ERROR);
        });
        $curl = $this->createMock(Curl::class);
        $curl->httpStatusCode = 200;
        $curl->method('post')->willReturn((object) [
            'id' => 'chatcmpl-test',
            'choices' => [(object) [
                'message' => (object) ['content' => 'Private generated response'],
                'finish_reason' => 'stop',
            ]],
            'usage' => (object) [
                'prompt_tokens' => 20,
                'completion_tokens' => 30,
                'total_tokens' => 50,
                'prompt_tokens_details' => (object) ['cached_tokens' => 5],
                'completion_tokens_details' => (object) ['reasoning_tokens' => 8],
            ],
        ]);

        $result = (new OpenAI($curl, $logger))->complete(
            new EmailPrompt('System prompt', 'Private draft', 'subject'),
            ['apiKey' => 'secret-api-key', 'model' => 'gpt-4.1']
        );

        self::assertSame('Private generated response', $result);
        self::assertSame('subject', $lines[0]['operation']);
        self::assertSame('Private draft', $lines[0]['prompt']['user']);
        self::assertSame('chatcmpl-test', $lines[1]['details']['provider_request_id']);
        self::assertSame(200, $lines[1]['details']['http_status']);
        self::assertSame(50, $lines[1]['usage']['total_tokens']);
        self::assertSame(5, $lines[1]['usage']['cached_tokens']);
        self::assertSame(8, $lines[1]['usage']['reasoning_tokens']);
        self::assertStringNotContainsString('secret-api-key', json_encode($lines));
        self::assertStringNotContainsString('Private generated response', json_encode($lines));
    }

    public function testDebugLogRecordsSafeErrorDetailsWithoutProviderMessage(): void
    {
        $lines = [];
        $logger = new RequestLogger(true, '42', static function (string $line) use (&$lines): void {
            $lines[] = json_decode($line, true, 512, \JSON_THROW_ON_ERROR);
        });
        $curl = $this->createMock(Curl::class);
        $curl->error = true;
        $curl->errorCode = 400;
        $curl->httpStatusCode = 400;
        $curl->errorMessage = 'Private provider response';
        $curl->response = (object) ['error' => (object) [
            'type' => 'invalid_request_error',
            'code' => 'unsupported_parameter',
            'message' => 'Private provider response',
        ]];
        $curl->method('post')->willReturn($curl->response);

        try {
            (new OpenAI($curl, $logger))->complete(
                new EmailPrompt('System', 'Private draft'),
                ['apiKey' => 'secret-api-key', 'model' => 'gpt-4.1']
            );
            self::fail('Expected provider error');
        } catch (ProviderException $e) {
            self::assertSame('error', $lines[1]['status']);
            self::assertSame(400, $lines[1]['details']['http_status']);
            self::assertSame('unsupported_parameter', $lines[1]['details']['provider_error_code']);
            self::assertStringNotContainsString('Private provider response', json_encode($lines));
            self::assertStringNotContainsString('secret-api-key', json_encode($lines));
        }
    }

    public function testGenerateEmailProviderException()
    {
        $mockCurl = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $openAI = new OpenAI($mockCurl);

        Settings::setProviderConfig(['apiKey' => 'test-api-key', 'model' => 'test-model']);

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('No email content found');

        $openAI->generateEmail($this->requestData, $this->prompt);
    }

    public function testSendRequestSetters()
    {
        $this->requestData->setSignaturePresent(true);

        $curlMock = $this->getMockBuilder(Curl::class)
            ->onlyMethods(['setHeader', 'setOpts', 'post'])
            ->getMock()
        ;
        $curlMock->method('post')->willReturn((object) [
            'choices' => [(object) ['message' => (object) ['content' => 'Generated email']]],
        ]);

        $OpenAi = new OpenAI($curlMock);

        Settings::setProviderConfig([
            'apiKey' => 'test-api-key',
            'model' => 'model-test',
        ]);

        $curlMock->expects(self::exactly(2))
            ->method('setHeader')
            ->withConsecutive(
                ['Content-Type', 'application/json'],
                ['Authorization', 'Bearer test-api-key']
            )
        ;

        $curlMock->expects(self::once())
            ->method('setOpts')
            ->with([\CURLOPT_TIMEOUT => 60,
                \CURLOPT_SSL_VERIFYPEER => true,
                \CURLOPT_SSL_VERIFYHOST => 2, ])
        ;

        self::assertInstanceOf(Respond::class, $OpenAi->generateEmail($this->requestData, $this->prompt));
    }

    public function testSendRequestPostMethod()
    {
        Settings::setProviderConfig(['apiKey' => 'test-api-key', 'model' => 'gpt-4.1']);

        $curlMock = $this->getMockBuilder(Curl::class)
            ->onlyMethods(['post'])
            ->getMock()
        ;

        $OpenAi = new OpenAI($curlMock);

        $curlMock->expects(self::once())
            ->method('post')
            ->with(
                self::equalTo('https://api.openai.com/v1/chat/completions'),
                self::equalTo([
                    'model' => 'gpt-4.1',
                    'messages' => [
                        ['role' => 'system', 'content' => 'System instruction'],
                        ['role' => 'user', 'content' => 'Email instruction'],
                    ],
                    'max_tokens' => 2000,
                    'temperature' => 0.5,
                    'n' => 1,
                    'stream' => false, ])
            )
        ;

        try {
            $OpenAi->generateEmail($this->requestData, $this->prompt);
        } catch (ProviderException $e) {
        }
    }

    /**
     * @dataProvider provideModernModelsUseSupportedChatParametersCases
     */
    public function testModernModelsUseSupportedChatParameters(string $model, ?string $reasoningEffort, string $apiUrl): void
    {
        Settings::setProviderConfig([
            'apiKey' => 'test-api-key',
            'model' => $model,
            'apiUrl' => $apiUrl,
        ]);

        $curlMock = $this->getMockBuilder(Curl::class)
            ->onlyMethods(['post'])
            ->getMock()
        ;

        $curlMock->expects(self::once())
            ->method('post')
            ->with(
                self::equalTo($apiUrl),
                self::callback(static function (array $payload) use ($model, $reasoningEffort): bool {
                    self::assertSame($model, $payload['model']);
                    self::assertSame([
                        ['role' => 'developer', 'content' => 'System instruction'],
                        ['role' => 'user', 'content' => 'Email instruction'],
                    ], $payload['messages']);
                    self::assertSame(2000, $payload['max_completion_tokens']);
                    self::assertArrayNotHasKey('max_tokens', $payload);
                    self::assertArrayNotHasKey('temperature', $payload);

                    if ($reasoningEffort === null) {
                        self::assertArrayNotHasKey('reasoning_effort', $payload);
                    } else {
                        self::assertSame($reasoningEffort, $payload['reasoning_effort']);
                    }

                    return true;
                })
            )
            ->willReturn((object) [
                'choices' => [(object) ['message' => (object) ['content' => 'Generated email']]],
            ])
        ;

        self::assertInstanceOf(Respond::class, (new OpenAI($curlMock))->generateEmail($this->requestData, $this->prompt));
    }

    /**
     * @return iterable<string, array{string, ?string, string}>
     */
    public static function provideModernModelsUseSupportedChatParametersCases(): iterable
    {
        $defaultUrl = 'https://api.openai.com/v1/chat/completions';

        return [
            'GPT-5' => ['gpt-5', 'minimal', $defaultUrl],
            'GPT-5 mini' => ['gpt-5-mini', 'minimal', $defaultUrl],
            'GPT-5 newer family' => ['gpt-5.4', null, $defaultUrl],
            'GPT-5 pro' => ['gpt-5-pro', null, $defaultUrl],
            'GPT-6 Astra' => ['gpt-6-astra', 'low', $defaultUrl],
            'GPT-6 Sol' => ['gpt-6-sol', 'low', $defaultUrl],
            'GPT-6 Luna custom endpoint' => ['gpt-6-luna', 'low', 'https://proxy.example/chat/completions'],
        ];
    }

    public function testEmptyModernResponseExplainsOutputTokenLimit(): void
    {
        Settings::setProviderConfig(['apiKey' => 'test-api-key', 'model' => 'gpt-6-astra']);

        $curlMock = $this->getMockBuilder(Curl::class)
            ->onlyMethods(['post'])
            ->getMock()
        ;
        $curlMock->method('post')->willReturn((object) [
            'choices' => [(object) [
                'message' => (object) ['content' => ''],
                'finish_reason' => 'length',
            ]],
        ]);

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Increase aiDefaultMaxTokens');

        (new OpenAI($curlMock))->generateEmail($this->requestData, $this->prompt);
    }

    public function testSendRequestUnauthorized()
    {
        $curlMock = $this->getMockBuilder(Curl::class)
            ->onlyMethods(['post'])
            ->getMock()
        ;
        $curlMock->error = true;
        $curlMock->errorMessage = 'HTTP/1.1 401 Unauthorized';
        $curlMock->method('post')->willReturn((object) []);

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('APICurl: HTTP/1.1 401 Unauthorized');

        (new OpenAI($curlMock))->generateEmail($this->requestData, $this->prompt);
    }

    public function testSendRequestThrowable()
    {
        $this->requestData->setSignaturePresent(true);

        $mockCurl = $this->getMockBuilder(Curl::class)
            ->onlyMethods(['post'])
            ->getMock()
        ;

        $mockCurl->expects(self::once())
            ->method('post')
            ->willThrowException(new \Exception(\DivisionByZeroError::class))
        ;

        $openAI = new OpenAI($mockCurl);

        $this->expectException(\Throwable::class);
        $this->expectExceptionMessage('DivisionByZeroError');

        $openAI->generateEmail($this->requestData, $this->prompt);
    }
}
