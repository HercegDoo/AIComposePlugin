<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Providers;

use Curl\Curl;
use DG\BypassFinals;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
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
    protected function setUp(): void
    {
        parent::setUp();

        Settings::setStyles(['professional', 'default' => 'casual', 'assertive', 'enthusiastic', 'funny', 'informational', 'persuasive']);

        Settings::setLengths(['short', 'default' => 'medium', 'long']);

        Settings::setLanguages(['default' => 'Bosnian', 'Croatian', 'German', 'Dutch']);

        Settings::setDefaultMaxTokens(2000);
        Settings::setProviderConfig(['apiKey' => 'test-api-key', 'model' => 'model-test']);
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

        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstrukcija');
        $requestData->setSignaturePresent(false);
        $requestData->setMultipleRecipients(false);

        Settings::setProviderConfig(['apiKey' => 'test-api-key', 'model' => 'test-model']);

        try {
            $openAI->generateEmail($requestData);
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

        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstrukcija');
        $requestData->setSignaturePresent(false);
        $requestData->setMultipleRecipients(false);
        $settingsMock = $this->createMock(Settings::class);

        $openAI->setError('dummyError');

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('dummyError');
        $openAI->generateEmail($requestData, $settingsMock);
    }

    public function testGenerateEmailReturnType()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'jabuka', 'casual', 'medium', 'low', 'Bosnian');
        $requestData->setInstruction('afdsafsd');
        $requestData->setSignaturePresent(false);
        $requestData->setMultipleRecipients(false);

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

        $return = $OpenAI->generateEmail($requestData);

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
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstrukcija');
        $requestData->setSignaturePresent(false);
        $requestData->setMultipleRecipients(false);

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('No email content found');

        $openAI->generateEmail($requestData);
    }

    public function testPromptNoFixDefault()
    {
        $OpenAI = new OpenAI();
        $privateMethodInvoker = ReflectionHelper::getPrivateMethodInvoker($OpenAI, 'prompt');

        $requestData = RequestData::make('Meho', 'Muhamed', 'TestInstrukcija');
        $requestData->setSignaturePresent(false);
        $requestData->setMultipleRecipients(false);
        $result = $privateMethodInvoker($requestData);

        self::assertSame('Create a casual email with the following specifications: Without a subject *Recipient: Meho *Sender: Muhamed *Language: Bosnian *Length: medium. Compose a well-structured email based on this instruction: TestInstrukcija. The instruction should be rewritten in the tone and format of a casual email to a reader. Ensure that the generated email does not contain the exact same text as the instruction. If the instruction contains pronouns (like \'he\', \'she\', \'they\', etc.), assume they refer to the recipient unless specified otherwise. The number of words should be 70 to 150 words . Do not write the subject if provided, it is only there for your context. Only greet the recipient, never the sender. IMPORTANT: Format the email as a standard email, ensuring it is well-structured and visually appealing, regardless of the number of words provided. The format should be as follows:
Greeting

Content

Closing Greeting
', $result);
    }

    public function testPromptNoFixCustom()
    {
        $OpenAi = new OpenAI();
        $privateMethodInvoker = ReflectionHelper::getPrivateMethodInvoker($OpenAi, 'prompt');

        $requestData = RequestData::make('Ime1', 'Ime2', 'Sastavi Mail', 'professional', 'long', 'low', 'Spanish');
        $requestData->setSignaturePresent(true);
        $requestData->setMultipleRecipients(false);
        $result = $privateMethodInvoker($requestData);

        self::assertSame('Create a professional email with the following specifications: Without a subject *Recipient: Ime1 *Sender: Ime2 *Language: Spanish *Length: long. Compose a well-structured email based on this instruction: Sastavi Mail. The instruction should be rewritten in the tone and format of a professional email to a reader. Ensure that the generated email does not contain the exact same text as the instruction. If the instruction contains pronouns (like \'he\', \'she\', \'they\', etc.), assume they refer to the recipient unless specified otherwise. The number of words should be over 150 words. Do not write the subject if provided, it is only there for your context. Only greet the recipient, never the sender. IMPORTANT: Format the email as a standard email, ensuring it is well-structured and visually appealing, regardless of the number of words provided. The format should be as follows:
Greeting

Content

Closing Greeting
CRUCIAL: "Write an email without signing it or including any identifying information after the greeting, including no names or titles. Only include the message and greeting, but leave the signature and closing blank."', $result);
    }

    public function testPromptFixDefault()
    {
        $OpenAi = new OpenAI();
        $privateMethodInvoker = ReflectionHelper::getPrivateMethodInvoker($OpenAi, 'prompt');

        $requestData = RequestData::make('Ime1', 'Ime2', 'SastaviMail');
        $requestData->setFixText('dummyprevgenemail', 'fixThisExample');
        $requestData->setPreviousConversation('prevConvo');
        $requestData->setMultipleRecipients(false);

        $result = $privateMethodInvoker($requestData);

        self::assertSame(' Write an identical email as this dummyprevgenemail, in the same language, but change only this text snippet from that same email: fixThisExample based on this instruction SastaviMail. Previous conversation: prevConvo.', $result);
    }

    public function testPromptFixCustom()
    {
        $OpenAi = new OpenAI();
        $privateMethodInvoker = ReflectionHelper::getPrivateMethodInvoker($OpenAi, 'prompt');

        $requestData = RequestData::make('Ime1', 'Ime2', 'SastaviMail', 'professional', 'long', 'low', 'Spanish');
        $requestData->setSubject('');
        $requestData->setFixText('dummyprevgenemail', 'fixThisExample');
        $requestData->setPreviousConversation('prevConvo');
        $requestData->setMultipleRecipients(false);

        $result = $privateMethodInvoker($requestData);

        self::assertSame(' Write an identical email as this dummyprevgenemail, in the same language, but change only this text snippet from that same email: fixThisExample based on this instruction SastaviMail. Previous conversation: prevConvo.', $result);
    }

    public function testSendRequestSetters()
    {
        $requestData = RequestData::make('Ime1', 'Ime2', 'SastaviMail');
        $requestData->setSignaturePresent(true);
        $requestData->setMultipleRecipients(false);
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
            $OpenAi->generateEmail($requestData);
        } catch (ProviderException $e) {
        }
    }

    public function testSendRequestPostMethod()
    {
        $requestData = RequestData::make('Ime1', 'Ime2', 'SastaviMail');
        $requestData->setMultipleRecipients(false);
        $requestData->setSignaturePresent(false);

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
                        ['role' => 'system', 'content' => 'You are a helpful personal assistant.'],
                        ['role' => 'user', 'content' => 'Create a casual email with the following specifications: Without a subject *Recipient: Ime1 *Sender: Ime2 *Language: Bosnian *Length: medium. Compose a well-structured email based on this instruction: SastaviMail. The instruction should be rewritten in the tone and format of a casual email to a reader. Ensure that the generated email does not contain the exact same text as the instruction. If the instruction contains pronouns (like \'he\', \'she\', \'they\', etc.), assume they refer to the recipient unless specified otherwise. The number of words should be 70 to 150 words . Do not write the subject if provided, it is only there for your context. Only greet the recipient, never the sender. IMPORTANT: Format the email as a standard email, ensuring it is well-structured and visually appealing, regardless of the number of words provided. The format should be as follows:
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
            $OpenAi->generateEmail($requestData);
        } catch (ProviderException $e) {
        }
    }

    public function testSendRequestUnathorized()
    {
        $requestData = RequestData::make('Ime1', 'Ime2', 'SastaviMail');
        $requestData->setSignaturePresent(true);
        $requestData->setMultipleRecipients(true);
        $OpenAi = new OpenAI();

        $this->expectException(ProviderException::class);
        $regex = '/HTTP\/(1\.1|2)\s401\s?(Unauthorized)?/';
        $this->expectExceptionMessageMatches($regex);

        $OpenAi->generateEmail($requestData);
    }

    public function testSendRequestNotFound()
    {
        $requestData = RequestData::make('Ime1', 'Ime2', 'SastaviMail');
        $requestData->setSignaturePresent(true);
        $requestData->setMultipleRecipients(false);
        $OpenAi = new OpenAI();

        $this->expectException(ProviderException::class);

        $OpenAi->generateEmail($requestData);
    }

    public function testSendRequestBadRequest()
    {
        $requestData = RequestData::make('Ime1', 'Ime2', 'SastaviMail');
        $requestData->setSignaturePresent(true);
        $requestData->setMultipleRecipients(false);
        $OpenAi = new OpenAI();

        ReflectionHelper::setPrivateProperty($OpenAi, 'creativityMap', [Settings::getCreativities()[0] => -55,
            Settings::getCreativities()[1] => -600,
            Settings::getCreativities()[2] => -10000, ]);

        $this->expectException(ProviderException::class);

        $OpenAi->generateEmail($requestData);
    }

    public function testSendRequestThrowable()
    {
        $requestData = RequestData::make('Ime1', 'Ime2', 'SastaviMail');
        $requestData->setSignaturePresent(true);
        $requestData->setMultipleRecipients(false);
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

        $openAI->generateEmail($requestData);
    }
}
