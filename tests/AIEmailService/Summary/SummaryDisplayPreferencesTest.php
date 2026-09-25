<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Summary;

use HercegDoo\AIComposePlugin\AIEmailService\Summary\SummaryDisplayPreferences;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SummaryDisplayPreferencesTest extends TestCase
{
    public function testHoverIsHiddenAndOpenedMessageUsesLongModeWithoutSavedChoices(): void
    {
        self::assertFalse(SummaryDisplayPreferences::isEnabled([], 'preview'));
        self::assertTrue(SummaryDisplayPreferences::isEnabled([], 'message'));
        self::assertSame('long', SummaryDisplayPreferences::choice([], SummaryDisplayPreferences::MESSAGE));
    }

    public function testEachViewCanBeHiddenIndependently(): void
    {
        self::assertFalse(SummaryDisplayPreferences::isEnabled(['summaryHover' => 'hide'], 'preview'));
        self::assertTrue(SummaryDisplayPreferences::isEnabled(['summaryHover' => 'hide'], 'message'));
        self::assertTrue(SummaryDisplayPreferences::isEnabled(['summaryHover' => 'show', 'summaryMessage' => 'hide'], 'preview'));
        self::assertFalse(SummaryDisplayPreferences::isEnabled(['summaryMessage' => 'hide'], 'message'));
        self::assertTrue(SummaryDisplayPreferences::isEnabled(['summaryMessage' => 'show'], 'message'));
        self::assertTrue(SummaryDisplayPreferences::isEnabled(['summaryMessage' => 'long'], 'message'));
        self::assertFalse(SummaryDisplayPreferences::isEnabled([], 'unknown'));
    }

    public function testInvalidStoredValuesUseEachViewDefault(): void
    {
        self::assertSame('hide', SummaryDisplayPreferences::choice(['summaryHover' => false], SummaryDisplayPreferences::HOVER));
        self::assertSame('long', SummaryDisplayPreferences::choice(['summaryMessage' => false], SummaryDisplayPreferences::MESSAGE));
        self::assertSame('hide', SummaryDisplayPreferences::choice(['summaryHover' => 'long'], SummaryDisplayPreferences::HOVER));
        self::assertFalse(SummaryDisplayPreferences::isValidFor('long', SummaryDisplayPreferences::HOVER));
        self::assertTrue(SummaryDisplayPreferences::isValidFor('long', SummaryDisplayPreferences::MESSAGE));
        self::assertFalse(SummaryDisplayPreferences::isValid('other'));
    }

    public function testOpenedMessageModeUsesTwoHundredWordBoundary(): void
    {
        $short = str_repeat('word ', 199);
        $long = str_repeat('word ', 200);

        self::assertFalse(SummaryDisplayPreferences::shouldSummarizeMessage([], $short));
        self::assertTrue(SummaryDisplayPreferences::shouldSummarizeMessage([], $long));
        self::assertFalse(SummaryDisplayPreferences::shouldSummarizeMessage(['summaryMessage' => 'hide'], $long));
        self::assertTrue(SummaryDisplayPreferences::shouldSummarizeMessage(['summaryMessage' => 'show'], $short));
    }
}
