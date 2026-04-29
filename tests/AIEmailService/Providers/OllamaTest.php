<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Providers;

use Curl\Curl;
use DG\BypassFinals;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\Ollama;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use HercegDoo\AIComposePlugin\TestSupport\ReflectionHelper;
use PHPUnit\Framework\TestCase;

BypassFinals::enable();

/**
 * @internal
 *
 * @coversNothing
 */
final class OllamaTest extends TestCase
{
    protected RequestData $requestData;

    protected function setUp(): void
    {
        parent::setUp();

        Settings::setStyles(['professional', 'default' => 'casual', 'assertive', 'enthusiastic', 'funny', 'informational', 'persuasive']);

        Settings::setLengths(['short', 'default' => 'medium', 'long']);

        Settings::setLanguages(['default' => 'Bosnian', 'Croatian', 'German', 'Dutch']);

        Settings::setDefaultMaxTokens(2000);
        Settings::setProviderConfig(['model' => 'model-test']);

        $this->requestData = RequestData::make('Meho', 'Muhi', 'TestInstrukcija');
        $this->requestData->setSignaturePresent(false);
        $this->requestData->setMultipleRecipients(false);
    }

    public function testSetError()
    {
        $ollama = new Ollama();
        self::assertNull($ollama->getErrors()[\count($ollama->getErrors()) - 1] ?? null);
        $ollama->setError('MyError');
        self::assertSame('MyError', $ollama->getErrors()[\count($ollama->getErrors()) - 1]);
        $ollama->setError('MyError1');
        self::assertSame('MyError1', $ollama->getErrors()[\count($ollama->getErrors()) - 1]);
        self::assertNull($ollama->setError('MyError'));
    }

    public function testGetError()
    {
        $ollama = new Ollama();
        self::assertIsArray($ollama->getErrors());
        self::assertCount(0, $ollama->getErrors());

        $ollama->setError('MyError');
        self::assertCount(1, $ollama->getErrors());
    }

    public function testHasError()
    {
        $ollama = new Ollama();
        self::assertFalse($ollama->hasErrors());
        $ollama->setError('MyError');
        self::assertTrue($ollama->hasErrors());
    }

    public function testGetProviderName()
    {
        $ollama = new Ollama();
        self::assertSame('Ollama', $ollama->getProviderName());
    }

    public function testGenerateEmailAssignPropertiesDefault()
    {
        $mockCurl = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $ollama = new Ollama($mockCurl);

        Settings::setProviderConfig(['model' => 'test-model']);

        try {
            $ollama->generateEmail($this->requestData);
        } catch (ProviderException $exception) {
            self::assertSame('test-model', ReflectionHelper::getPrivateProperty($ollama, 'model'));
            self::assertSame(2000, ReflectionHelper::getPrivateProperty($ollama, 'maxTokens'));
            self::assertSame(0.5, ReflectionHelper::getPrivateProperty($ollama, 'creativity'));
            self::assertSame('http://localhost:11434/v1/chat/completions', ReflectionHelper::getPrivateProperty($ollama, 'apiUrl'));
        }
    }

    public function testGenerateEmailAssignPropertiesCustomApiUrl()
    {
        $mockCurl = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $ollama = new Ollama($mockCurl);

        Settings::setProviderConfig(['model' => 'test-model', 'apiUrl' => 'http://192.168.1.10:11434/v1/chat/completions']);

        try {
            $ollama->generateEmail($this->requestData);
        } catch (ProviderException $exception) {
            self::assertSame('http://192.168.1.10:11434/v1/chat/completions', ReflectionHelper::getPrivateProperty($ollama, 'apiUrl'));
        }
    }

    public function testGenerateEmailErrorException()
    {
        $mockCurl = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        Settings::setProviderConfig(['model' => 'test-model']);

        $ollama = new Ollama($mockCurl);

        $settingsMock = $this->createMock(Settings::class);

        $ollama->setError('dummyError');

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('dummyError');
        $ollama->generateEmail($this->requestData, $settingsMock);
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
        $ollama = new Ollama($curlMock);

        $return = $ollama->generateEmail($this->requestData);

        self::assertInstanceOf(Respond::class, $return);
    }

    public function testGenerateEmailProviderException()
    {
        $mockCurl = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $ollama = new Ollama($mockCurl);

        Settings::setProviderConfig(['model' => 'test-model']);

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('No email content found');

        $ollama->generateEmail($this->requestData);
    }

    public function testPromptNoFixDefault()
    {
        $ollama = new Ollama();
        $privateMethodInvoker = ReflectionHelper::getPrivateMethodInvoker($ollama, 'prompt');

        $result = $privateMethodInvoker($this->requestData);

        self::assertSame('Create a casual email with the following specifications: Without a subject *Recipient: Meho *Sender: Muhi *Language: Bosnian *Length: medium. Compose a well-structured email based on this instruction: TestInstrukcija. The instruction should be rewritten in the tone and format of a casual email to a reader.  If the instruction contains pronouns (like \'he\', \'she\', \'they\', etc.), assume they refer to the recipient unless specified otherwise. The number of words should be 70 to 150 words . Do not write the subject if provided, it is only there for your context. Only greet the recipient, never the sender. The format should be as follows:
Greeting

Content

Closing Greeting
', $result);
    }

    public function testSendRequestSetters()
    {
        $this->requestData->setSignaturePresent(true);

        $curlMock = $this->getMockBuilder(Curl::class)
            ->onlyMethods(['setHeader', 'setOpts'])
            ->getMock()
        ;

        $ollama = new Ollama($curlMock);

        Settings::setProviderConfig([
            'model' => 'model-test',
        ]);

        $curlMock->expects(self::once())
            ->method('setHeader')
            ->with('Content-Type', 'application/json')
        ;

        $curlMock->expects(self::once())
            ->method('setOpts')
            ->with([\CURLOPT_TIMEOUT => 60,
                // not verifying the ssl certificate
                \CURLOPT_SSL_VERIFYPEER => false,
                \CURLOPT_SSL_VERIFYHOST => false, ])
        ;

        try {
            $ollama->generateEmail($this->requestData);
        } catch (ProviderException $e) {
        }
    }

    public function testSendRequestPostMethod()
    {
        $curlMock = $this->getMockBuilder(Curl::class)
            ->onlyMethods(['post'])
            ->getMock()
        ;

        $ollama = new Ollama($curlMock);

        $curlMock->expects(self::once())
            ->method('post')
            ->with(
                self::equalTo('http://localhost:11434/v1/chat/completions'),
                self::equalTo([
                    'model' => 'model-test',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful personal assistant.'],
                        ['role' => 'user', 'content' => 'Create a casual email with the following specifications: Without a subject *Recipient: Meho *Sender: Muhi *Language: Bosnian *Length: medium. Compose a well-structured email based on this instruction: TestInstrukcija. The instruction should be rewritten in the tone and format of a casual email to a reader.  If the instruction contains pronouns (like \'he\', \'she\', \'they\', etc.), assume they refer to the recipient unless specified otherwise. The number of words should be 70 to 150 words . Do not write the subject if provided, it is only there for your context. Only greet the recipient, never the sender. The format should be as follows:
Greeting

Content

Closing Greeting
',
                        ],
                    ],
                    'max_tokens' => 2000,
                    'temperature' => 0.5,
                    'n' => 1,
                    'stream' => false, ])
            )
        ;

        try {
            $ollama->generateEmail($this->requestData);
        } catch (ProviderException $e) {
        }
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

        $ollama = new Ollama($mockCurl);

        $this->expectException(\Throwable::class);
        $this->expectExceptionMessage('DivisionByZeroError');

        $ollama->generateEmail($this->requestData);
    }
}
