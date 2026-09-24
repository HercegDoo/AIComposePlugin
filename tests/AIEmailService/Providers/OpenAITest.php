<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Providers;

use Curl\Curl;
use DG\BypassFinals;
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
            ->onlyMethods(['setHeader', 'setOpts'])
            ->getMock()
        ;

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
                // not verifying the ssl certificate
                \CURLOPT_SSL_VERIFYPEER => false,
                \CURLOPT_SSL_VERIFYHOST => false, ])
        ;

        try {
            $OpenAi->generateEmail($this->requestData, $this->prompt);
        } catch (ProviderException $e) {
        }
    }

    public function testSendRequestPostMethod()
    {
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
                    'model' => 'model-test',
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

    public function testSendRequestUnathorized()
    {
        $this->requestData->setSignaturePresent(true);
        $this->requestData->setMultipleRecipients(true);
        $OpenAi = new OpenAI();

        $this->expectException(ProviderException::class);
        $regex = '/HTTP\/(1\.1|2)\s401\s?(Unauthorized)?/';
        $this->expectExceptionMessageMatches($regex);

        $OpenAi->generateEmail($this->requestData, $this->prompt);
    }

    public function testSendRequestNotFound()
    {
        $this->requestData->setSignaturePresent(true);

        $OpenAi = new OpenAI();

        $this->expectException(ProviderException::class);

        $OpenAi->generateEmail($this->requestData, $this->prompt);
    }

    public function testSendRequestBadRequest()
    {
        $this->requestData->setSignaturePresent(true);

        $OpenAi = new OpenAI();

        ReflectionHelper::setPrivateProperty($OpenAi, 'creativityMap', [Settings::getCreativities()[0] => -55,
            Settings::getCreativities()[1] => -600,
            Settings::getCreativities()[2] => -10000, ]);

        $this->expectException(ProviderException::class);

        $OpenAi->generateEmail($this->requestData, $this->prompt);
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
