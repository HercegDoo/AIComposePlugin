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
        $settings = Settings::getSettingsInstance($dummyProvider);

        $aiEmailService = AIEmailService::createAIEmailService($settings);

        self::assertSame($dummyProvider, ReflectionHelper::getPrivateProperty($aiEmailService, 'provider'));
        self::assertSame($settings, ReflectionHelper::getPrivateProperty($aiEmailService, 'settings'));
    }

    public function testGenerateEmailServiceDummy()
    {
        $dummyProvider = new DummyProvider();
        $settings = Settings::getSettingsInstance($dummyProvider);
        $aiEmailService = AIEmailService::createAIEmailService($settings);
        $requestData = RequestData::make('Meho', 'Muhi', 'InstrukcijaDummy', null, null, null, null);

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

        $settings = Settings::getSettingsInstance($dummyProviderMock);
        $aiEmailService = AIEmailService::createAIEmailService($settings);
        $requestData = RequestData::make('Meho', 'Muhi', 'InstrukcijaDummy', null, null, null, null);

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

        $settings = Settings::getSettingsInstance($dummyProviderMock);
        $aiEmailService = AIEmailService::createAIEmailService($settings);
        $requestData = RequestData::make('Meho', 'Muhi', 'InstrukcijaDummy', null, null, null, null);

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

        $settings = Settings::getSettingsInstance($openAiMock);

        $aiEmailService = AIEmailService::createAIEmailService($settings);

        $requestData = RequestData::make('Meho', 'Muhi', 'InstrukcijaDummy', null, null, null, null);

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

        $settings = Settings::getSettingsInstance($openAiMock);

        $aiEmailService = AIEmailService::createAIEmailService($settings);

        $requestData = RequestData::make('Meho', 'Muhi', 'Instrukcija', null, null, null, null);

        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage('Provider Exception');
        $aiEmailService->generateEmail($requestData);
    }
}
