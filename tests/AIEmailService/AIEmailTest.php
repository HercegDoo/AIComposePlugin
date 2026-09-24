<?php

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService;

use HercegDoo\AIComposePlugin\AIEmailService\AIEmail;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\EmailPrompt;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\PromptBuilderInterface;
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

        if (!\defined('PHPUNIT_RUNNING')) {
            \define('PHPUNIT_RUNNING', true); // Definišite konstantu samo ako nije već definisana
        }

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

    public function testGenerateEmailPassesSharedPromptToProvider(): void
    {
        $provider = new class extends DummyProvider {
            public ?EmailPrompt $receivedPrompt = null;

            public function generateEmail(RequestData $requestData, EmailPrompt $prompt): Respond
            {
                $this->receivedPrompt = $prompt;

                return new Respond('Generated email');
            }
        };

        $builder = new class implements PromptBuilderInterface {
            public function build(RequestData $requestData): EmailPrompt
            {
                return new EmailPrompt('Shared system instruction', 'Shared user instruction');
            }
        };

        $this->setPrivateProperty(Settings::class, 'provider', $provider);

        $response = AIEmail::generate(self::$requestData, $builder);

        self::assertSame('Generated email', $response->getBody());
        self::assertInstanceOf(EmailPrompt::class, $provider->receivedPrompt);
        self::assertSame('Shared system instruction', $provider->receivedPrompt->getSystemInstruction());
        self::assertSame('Shared user instruction', $provider->receivedPrompt->getUserInstruction());
    }

    public function testGenerateSubjectUsesDedicatedPromptAndCleansProviderOutput(): void
    {
        $provider = new class extends DummyProvider {
            public ?EmailPrompt $receivedPrompt = null;

            public function generateEmail(RequestData $requestData, EmailPrompt $prompt): Respond
            {
                $this->receivedPrompt = $prompt;

                return new Respond("Subject: <strong>Project update</strong>\r\nIgnore this line");
            }
        };

        $this->setPrivateProperty(Settings::class, 'provider', $provider);

        self::assertSame('Project update', AIEmail::generateSubject(self::$requestData, 'Draft about project progress', 'Older subject'));
        self::assertStringContainsString('Draft about project progress', $provider->receivedPrompt->getUserInstruction());
        self::assertStringContainsString('Older subject', $provider->receivedPrompt->getUserInstruction());
    }

    public function testGenerateSubjectRejectsEmptyProviderContent(): void
    {
        $provider = new class extends DummyProvider {
            public function generateEmail(RequestData $requestData, EmailPrompt $prompt): Respond
            {
                return new Respond("\n");
            }
        };

        $this->setPrivateProperty(Settings::class, 'provider', $provider);
        $this->expectException(ProviderException::class);
        AIEmail::generateSubject(self::$requestData, 'Draft');
    }

    public function testNormalizeSubjectRemovesMarkdownLabelAndAdditionalLines(): void
    {
        self::assertSame('Project update', AIEmail::normalizeSubject("**Subject:** Project update\nSecond line"));
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

            public function generateEmail(RequestData $requestData, EmailPrompt $prompt): Respond
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
