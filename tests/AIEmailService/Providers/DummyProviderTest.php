<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Providers;

use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\DummyProvider;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class DummyProviderTest extends TestCase
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

    public function testGetProviderNameDummy()
    {
        $dummyProvider = new DummyProvider();
        self::assertSame('Dummy Provider', $dummyProvider->getProviderName());
    }

    public function testGenerateEmailReturnDummy()
    {
        $requestData = RequestData::make('Meho', 'Muhi', 'TestInstrukcija');
        $dummyProvider = new DummyProvider();
        $settings = new Settings($dummyProvider);

        self::assertInstanceOf(Respond::class, $dummyProvider->generateEmail($requestData, $settings));
        self::assertSame('
            This is a dummy response to your request.
            Sender: Muhi
            Receiver: Meho
            Instructions: TestInstrukcija
            ', ($dummyProvider->generateEmail($requestData, $settings))->getBody());
    }
}
