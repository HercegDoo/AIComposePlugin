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
    public function testBothViewsAreShownForExistingUsers(): void
    {
        self::assertTrue(SummaryDisplayPreferences::isEnabled([], 'preview'));
        self::assertTrue(SummaryDisplayPreferences::isEnabled([], 'message'));
    }

    public function testEachViewCanBeHiddenIndependently(): void
    {
        self::assertFalse(SummaryDisplayPreferences::isEnabled(['summaryHover' => 'hide'], 'preview'));
        self::assertTrue(SummaryDisplayPreferences::isEnabled(['summaryHover' => 'hide'], 'message'));
        self::assertTrue(SummaryDisplayPreferences::isEnabled(['summaryMessage' => 'hide'], 'preview'));
        self::assertFalse(SummaryDisplayPreferences::isEnabled(['summaryMessage' => 'hide'], 'message'));
        self::assertFalse(SummaryDisplayPreferences::isEnabled([], 'unknown'));
    }

    public function testInvalidStoredValuesFallBackToShow(): void
    {
        self::assertSame('show', SummaryDisplayPreferences::choice(['summaryHover' => false], SummaryDisplayPreferences::HOVER));
        self::assertFalse(SummaryDisplayPreferences::isValid('other'));
    }
}
