<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Translation;

use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\EmailPrompt;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\CompletionProviderInterface;
use HercegDoo\AIComposePlugin\AIEmailService\Translation\TranslationService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class TranslationServiceTest extends TestCase
{
    public function testTranslatesFullPassageWithEnoughOutputBudget(): void
    {
        $provider = $this->createMock(CompletionProviderInterface::class);
        $provider->expects(self::once())->method('complete')->with(
            self::callback(static function (EmailPrompt $prompt): bool {
                return str_contains($prompt->getUserInstruction(), 'Hallo, wie geht es dir?')
                    && str_contains($prompt->getUserInstruction(), 'en_US');
            }),
            self::callback(static function (array $config): bool {
                return $config['maxTokens'] >= 4096;
            })
        )->willReturn('{"translation":"Hello, how are you?\n\nRegards"}');

        self::assertSame("Hello, how are you?\n\nRegards", (new TranslationService($provider, ['maxTokens' => 1200]))
            ->translate('Hallo, wie geht es dir?', 'en_US'));
    }

    public function testRejectsEmptyTranslation(): void
    {
        $provider = $this->createMock(CompletionProviderInterface::class);
        $provider->method('complete')->willReturn('{"translation":""}');

        $this->expectException(ProviderException::class);
        (new TranslationService($provider, []))->translate('Hallo', 'en_US');
    }
}
