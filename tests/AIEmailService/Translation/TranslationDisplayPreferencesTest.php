<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Translation;

use HercegDoo\AIComposePlugin\AIEmailService\Translation\TranslationDisplayPreferences;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class TranslationDisplayPreferencesTest extends TestCase
{
    public function testTranslationIsShownByDefaultAndCanBeHidden(): void
    {
        self::assertTrue(TranslationDisplayPreferences::isEnabled([]));
        self::assertFalse(TranslationDisplayPreferences::isEnabled(['translationMessage' => 'hide']));
        self::assertTrue(TranslationDisplayPreferences::isEnabled(['translationMessage' => 'show']));
    }
}
