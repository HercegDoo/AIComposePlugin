<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService;

use DG\BypassFinals;
use HercegDoo\AIComposePlugin\AIEmailService\AIEmailService;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\DummyProvider;
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
final class AIEmailServiceTest extends TestCase
{
    public function testConstructAiEmailService()
    {
        $dummyProvider = new DummyProvider();
        $settings = new Settings($dummyProvider);
        $aiEmailService = new AIEmailService($settings);

        self::assertSame($dummyProvider, ReflectionHelper::getPrivateProperty($aiEmailService, 'provider'));
        self::assertSame($settings, ReflectionHelper::getPrivateProperty($aiEmailService, 'settings'));
    }

    public function testGenerateEmailServiceDummy()
    {
        $dummyProvider = new DummyProvider();
        $settings = new Settings($dummyProvider);
        $aiEmailService = new AIEmailService($settings);
        $requestData = new RequestData('Meho', 'Muhi', 'InstrukcijaDummy');

        self::assertInstanceOf(Respond::class, $aiEmailService->generateEmail($requestData, $settings));
    }

    public function testGenerateEmailServiceDummyPrEx()
    {
        $dummyProviderMock = $this->getMockBuilder(DummyProvider::class)
            ->onlyMethods(['generateEmail'])
            ->getMock()
        ;

        $dummyProviderMock->expects(self::once())
            ->method('generateEmail')
            ->willThrowException(new \InvalidArgumentException('Invalid argument'))
        ;

        $settings = new Settings($dummyProviderMock);
        $aiEmailService = new AIEmailService($settings);
        $requestData = new RequestData('Meho', 'Muhi', 'InstrukcijaDummy');

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('General: Invalid argument');
        $aiEmailService->generateEmail($requestData);
    }

    public function testGenerateEmailServiceDummyThEx()
    {
        $dummyProviderMock = $this->getMockBuilder(DummyProvider::class)
            ->onlyMethods(['generateEmail'])
            ->getMock()
        ;

        $dummyProviderMock->expects(self::once())
            ->method('generateEmail')
            ->willThrowException(new ProviderException('Provider Exception'))
        ;

        $settings = new Settings($dummyProviderMock);
        $aiEmailService = new AIEmailService($settings);
        $requestData = new RequestData('Meho', 'Muhi', 'InstrukcijaDummy');

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Provider Exception');
        $aiEmailService->generateEmail($requestData);
    }


    public function testGenerateEmailServiceOpenAIPrEx()
    {
        $openAiMock = $this->getMockBuilder(OpenAI::class)
            ->onlyMethods(['generateEmail'])
            ->getMock()
        ;

        $openAiMock->expects(self::once())
            ->method('generateEmail')
            ->willThrowException(new \InvalidArgumentException('Invalid argument'))
        ;

        $settings = new Settings($openAiMock);

        $aiEmailService = new AIEmailService($settings);

        $requestData = new RequestData('Meho', 'Muhi', 'Instrukcija');

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('General: Invalid argument');
        $aiEmailService->generateEmail($requestData);
    }

    public function testGenerateEmailServiceOpenAIThEx()
    {
        $openAiMock = $this->getMockBuilder(OpenAI::class)
            ->onlyMethods(['generateEmail'])
            ->getMock()
        ;

        $openAiMock->expects(self::once())
            ->method('generateEmail')
            ->willThrowException(new ProviderException('Provider Exception'))
        ;

        $settings = new Settings($openAiMock);

        $aiEmailService = new AIEmailService($settings);

        $requestData = new RequestData('Meho', 'Muhi', 'Instrukcija');

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Provider Exception');
        $aiEmailService->generateEmail($requestData);
    }
}
