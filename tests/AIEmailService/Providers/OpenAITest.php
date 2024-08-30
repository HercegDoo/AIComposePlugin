<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Providers;

use Curl\Curl;
use DG\BypassFinals;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
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

        $requestData = new RequestData('Meho', 'Muhi', 'TestInstrukcija');
        $settingsMock = $this->createMock(Settings::class);

        $settingsMock->providerOpenAI = [
            'apiKey' => 'test-api-key',
            'model' => 'test-model',
        ];

        try {
            $openAI->generateEmail($requestData, $settingsMock);
        } catch (ProviderException $exception) {
            self::assertSame('test-api-key', ReflectionHelper::getPrivateProperty($openAI, 'apiKey'));
            self::assertSame('test-model', ReflectionHelper::getPrivateProperty($openAI, 'model'));
            self::assertSame(2000, ReflectionHelper::getPrivateProperty($openAI, 'maxTokens'));
            self::assertSame(0.5, ReflectionHelper::getPrivateProperty($openAI, 'creativity'));
        }
    }

    public function testGenerateEmailAssignPropertyUndefinedCreativity()
    {
        $mockCurl = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $openAI = new OpenAI($mockCurl);
        ReflectionHelper::setPrivateProperty($openAI, 'creativityMap', [null, null, null]);

        $requestData = new RequestData('Meho', 'Muhi', 'TestInstrukcija');
        $settingsMock = $this->createMock(Settings::class);

        $settingsMock->providerOpenAI = [
            'apiKey' => 'test-api-key',
            'model' => 'test-model',
        ];

        try {
            $openAI->generateEmail($requestData, $settingsMock);
        } catch (ProviderException $exception) {
            self::assertSame(0.5, ReflectionHelper::getPrivateProperty($openAI, 'creativity'));
            ReflectionHelper::getPrivateProperty($openAI, 'creativityMap');
        }
    }

    public function testGenerateEmailErrorException()
    {
        $mockCurl = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $openAI = new OpenAI($mockCurl);

        $requestData = new RequestData('Meho', 'Muhi', 'TestInstrukcija');
        $settingsMock = $this->createMock(Settings::class);

        $openAI->setError('dummyError');

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('dummyError');
        $openAI->generateEmail($requestData, $settingsMock);
    }

    public function testGenerateEmailProviderException()
    {
        $mockCurl = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $openAI = new OpenAI($mockCurl);

        $requestData = new RequestData('Meho', 'Muhi', 'TestInstrukcija');
        $settingsMock = $this->createMock(Settings::class);

        $settingsMock->providerOpenAI = [
            'apiKey' => 'test-api-key',
            'model' => 'test-model',
        ];

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('No email content found');

        $openAI->generateEmail($requestData, $settingsMock);
    }

    public function testPromptNoFixDefault()
    {
        $OpenAI = new OpenAI();
        $privateMethodInvoker = ReflectionHelper::getPrivateMethodInvoker($OpenAI, 'prompt');

        $requestData = new RequestData('Meho', 'Muhamed', 'TestInstrukcija');

        $result = $privateMethodInvoker($requestData);

        self::assertSame('Write a casual email without a subject' .
            ' to Meho' .
            ' from Muhamed.' .
            ' Email length: medium.' .
            ' Email language: Bosnian.' .
            ' Email content: TestInstrukcija.', $result);
    }

    public function testPromptNoFixCustom()
    {
        $OpenAi = new OpenAI();
        $privateMethodInvoker = ReflectionHelper::getPrivateMethodInvoker($OpenAi, 'prompt');

        $requestData = new RequestData('Ime1', 'Ime2', 'Sastavi Mail', 'professional', 'long', 'low', 'Spanish');

        $result = $privateMethodInvoker($requestData);

        self::assertSame('Write a professional email without a subject' .
            ' to Ime1' .
            ' from Ime2.' .
            ' Email length: long.' .
            ' Email language: Spanish.' .
            ' Email content: Sastavi Mail.', $result);
    }

    public function testPromptFixDefault()
    {
        $OpenAi = new OpenAI();
        $privateMethodInvoker = ReflectionHelper::getPrivateMethodInvoker($OpenAi, 'prompt');

        $requestData = new RequestData('Ime1', 'Ime2', 'SastaviMail');
        $requestData->setFixText('dummyprevgenemail', 'fixThisExample');
        $requestData->setPreviousConversation('prevConvo');

        $result = $privateMethodInvoker($requestData);

        self::assertSame('Write a casual email without a subject' .
            ' to Ime1' .
            ' from Ime2.' .
            ' Email length: medium.' .
            ' Email language: Bosnian.' .
            ' Email content: SastaviMail.' .
            ' Write the same email as this dummyprevgenemail but change this text snippet from that same email: fixThisExample based on this instruction SastaviMail.' .
            ' Previous conversation: prevConvo.', $result);
    }

    public function testPromptFixCustom()
    {
        $OpenAi = new OpenAI();
        $privateMethodInvoker = ReflectionHelper::getPrivateMethodInvoker($OpenAi, 'prompt');

        $requestData = new RequestData('Ime1', 'Ime2', 'SastaviMail', 'professional', 'long', 'low', 'Spanish');
        $requestData->setFixText('dummyprevgenemail', 'fixThisExample');
        $requestData->setPreviousConversation('prevConvo');

        $result = $privateMethodInvoker($requestData);

        self::assertSame('Write a professional email without a subject' .
            ' to Ime1' .
            ' from Ime2.' .
            ' Email length: long.' .
            ' Email language: Spanish.' .
            ' Email content: SastaviMail.' .
            ' Write the same email as this dummyprevgenemail but change this text snippet from that same email: fixThisExample based on this instruction SastaviMail.' .
            ' Previous conversation: prevConvo.', $result);
    }

    public function testSendRequestSetters()
    {
        $requestData = new RequestData('Ime1', 'Ime2', 'SastaviMail');

        $curlMock = $this->getMockBuilder(Curl::class)
            ->onlyMethods(['setHeader', 'setOpts'])
            ->getMock()
        ;

        $OpenAi = new OpenAI($curlMock);

        $settings = new Settings($OpenAi);
        ReflectionHelper::setPrivateProperty($settings, 'providerOpenAI', [
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
            $OpenAi->generateEmail($requestData, $settings);
        } catch (ProviderException $e) {
        }
    }

    public function testSendRequestPostMethod()
    {
        $requestData = new RequestData('Ime1', 'Ime2', 'SastaviMail');

        $curlMock = $this->getMockBuilder(Curl::class)
            ->onlyMethods(['post'])
            ->getMock()
        ;

        $OpenAi = new OpenAI($curlMock);

        $settings = new Settings($OpenAi);
        ReflectionHelper::setPrivateProperty($settings, 'providerOpenAI', [
            'apiKey' => 'test-api-key',
            'model' => 'model-test',
        ]);

        $curlMock->expects(self::once())
            ->method('post')
            ->with(
                self::equalTo('https://api.openai.com/v1/chat/completions'),
                self::equalTo([
                    'model' => 'model-test',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful personal assistant.'],
                        ['role' => 'user', 'content' => 'Write a casual email without a subject' .
                            ' to Ime1' .
                            ' from Ime2.' .
                            ' Email length: medium.' .
                            ' Email language: Bosnian.' .
                            ' Email content: SastaviMail.'],
                    ],
                    'max_tokens' => 2000,
                    'temperature' => 0.5,
                    'n' => 1,
                    'stream' => false, ])
            )
        ;

        try {
            $OpenAi->generateEmail($requestData, $settings);
        } catch (ProviderException $e) {
        }
    }

    public function testSendRequestUnathorized()
    {
        $requestData = new RequestData('Ime1', 'Ime2', 'SastaviMail');

        $OpenAi = new OpenAI();
        $settings = new Settings($OpenAi);

        ReflectionHelper::setPrivateProperty($settings, 'providerOpenAI', ['apiKey' => 'test-api-key',
            'model' => 'test-model']);

        $this->expectException(ProviderException::class);
        $regex = '/HTTP\/(1\.1|2)\s401\s?(Unauthorized)?/';
        $this->expectExceptionMessageMatches($regex);

        $OpenAi->generateEmail($requestData, $settings);
    }

    public function testSendRequestNotFound()
    {
        $requestData = new RequestData('Ime1', 'Ime2', 'SastaviMail');

        $OpenAi = new OpenAI();
        $settings = new Settings($OpenAi);
        $apiKey = $settings->providerOpenAI['apiKey'];

        ReflectionHelper::setPrivateProperty($settings, 'providerOpenAI', ['apiKey' => $apiKey,
            'model' => 'test-model']);

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('APICurl: HTTP/2 401');
        $OpenAi->generateEmail($requestData, $settings);
    }

    public function testSendRequestBadRequest()
    {
        $requestData = new RequestData('Ime1', 'Ime2', 'SastaviMail');

        $OpenAi = new OpenAI();
        $settings = new Settings($OpenAi);

        ReflectionHelper::setPrivateProperty($OpenAi, 'creativityMap', [Settings::CREATIVITY_LOW => -55,
            Settings::CREATIVITY_MEDIUM => -600,
            Settings::CREATIVITY_HIGH => -10000, ]);

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('APICurl: HTTP/2 401');
        $OpenAi->generateEmail($requestData, $settings);
    }

    public function testSendRequestThrowable()
    {
        $requestData = new RequestData('Ime1', 'Ime2', 'SastaviMail');

        $mockCurl = $this->getMockBuilder(Curl::class)
            ->onlyMethods(['post'])
            ->getMock()
        ;

        $mockCurl->expects(self::once())
            ->method('post')
            ->willThrowException(new \Exception(\DivisionByZeroError::class))
        ;

        $openAI = new OpenAI($mockCurl);
        $settings = new Settings($openAI);

        $this->expectException(\Throwable::class);
        $this->expectExceptionMessage('DivisionByZeroError');

        $openAI->generateEmail($requestData, $settings);
    }
}
