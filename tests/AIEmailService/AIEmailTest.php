<?php

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService;

use HercegDoo\AIComposePlugin\AIEmailService\AIEmail;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\DummyProvider;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use HercegDoo\AIComposePlugin\TestSupport\ReflectionHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class AIEmailTest extends TestCase
{
    use ReflectionHelper;

    private static RequestData $requestData;

    protected function setUp(): void
    {
        parent::setUp();

        Settings::setStyles(['professional', 'default' => 'casual', 'assertive', 'enthusiastic', 'funny', 'informational', 'persuasive']);

        Settings::setLengths(['short', 'default' => 'medium', 'long']);

        Settings::setLanguages(['default' => 'Bosnian', 'Croatian', 'German', 'Dutch']);

        Settings::setDefaultMaxTokens(2000);
        Settings::setProviderConfig(['apiKey' => 'test-api-key', 'model' => 'model-test']);

        self::$requestData = RequestData::make('meho', 'muhi', 'jabuka');
    }

    public function testGenerateEmailReturnType()
    {
        Settings::setProvider('DummyProvider');
        $return = AIEmail::generate(self::$requestData);
        self::assertInstanceOf(Respond::class, $return);
    }

    public function testGenerateEmailWithNonExistingProviderException()
    {
        $this->expectException(\InvalidArgumentException::class);
        Settings::setProvider('Nonexistprovider');
    }

    /**
     * @dataProvider provideGenererateEmailProviderExceptionCases
     */
    public function testGenererateEmailProviderException(\Exception $exception, string $message)
    {
        $dummyProvider = new class extends DummyProvider {
            public $exception;

            public function generateEmail(RequestData $requestData): Respond
            {
                throw $this->exception;
            }
        };
        $dummyProvider->exception = $exception;

        $this->setPrivateProperty(Settings::class, 'provider', $dummyProvider);
        $this->expectException(ProviderException::class);
        $this->expectExceptionMessage($message);
        AIEmail::generate(self::$requestData);
    }

    public static function provideGenererateEmailProviderExceptionCases(): iterable
    {
        yield [new \Exception('Test exception'), 'General: Test exception'];
        yield [new ProviderException('Test exception'), 'Test exception'];
    }
}
