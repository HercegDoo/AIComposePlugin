<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Summary;

use HercegDoo\AIComposePlugin\AIEmailService\Summary\SummaryTargetLanguage;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SummaryTargetLanguageTest extends TestCase
{
    public function testAutomaticChoiceFollowsCurrentInterfaceLanguage(): void
    {
        $available = ['en_US' => 'English', 'de_DE' => 'Deutsch'];

        self::assertSame(['locale' => 'de_DE', 'translate' => true], SummaryTargetLanguage::resolve('roundcube', 'de_DE', $available));
        self::assertSame(['locale' => 'en_US', 'translate' => true], SummaryTargetLanguage::resolve('roundcube', 'en_US', $available));
    }

    public function testUserCanChooseOriginalOrInstalledLanguage(): void
    {
        $available = ['en_US' => 'English', 'fr_FR' => 'Français'];

        self::assertSame(['locale' => 'en_US', 'translate' => false], SummaryTargetLanguage::resolve('original', 'en_US', $available));
        self::assertSame(['locale' => 'fr_FR', 'translate' => true], SummaryTargetLanguage::resolve('fr_FR', 'en_US', $available));
        self::assertFalse(SummaryTargetLanguage::isValid('xx_XX', $available));
        self::assertSame(['locale' => 'en_US', 'translate' => true], SummaryTargetLanguage::resolve('xx_XX', 'en_US', $available));
    }
}
