<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService;

use HercegDoo\AIComposePlugin\AIEmailService\Providers\OpenAI;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SettingsTest extends TestCase
{
    public function testGetCreativities()
    {
        self::assertSame(['low', 'medium', 'high'], Settings::getCreativities());
    }

    public function testSetAndGetLanguages(): void
    {
        Settings::setLanguages(['default' => 'Bosnian', 'Croatian', 'German', 'Dutch']);

        self::assertSame(['default' => 'Bosnian', 'Croatian', 'German', 'Dutch'], Settings::getLanguages());
    }

    public function testSetAndGetLengths()
    {
        Settings::setLengths(['short', 'default' => 'medium', 'long']);

        self::assertSame(['short', 'default' => 'medium', 'long'], Settings::getLengths());
    }

    public function testSetAndGetStyles()
    {
        Settings::setStyles(['professional', 'default' => 'casual', 'assertive', 'enthusiastic', 'funny', 'informational', 'persuasive']);

        self::assertSame(['professional', 'default' => 'casual', 'assertive', 'enthusiastic', 'funny', 'informational', 'persuasive'], Settings::getStyles());
    }

    public function testSetAndGetProviderOpenAI()
    {
        Settings::setProvider('OpenAI');
        self::assertInstanceOf(OpenAI::class, Settings::getProvider());
    }

    public function testSetProviderInvalidProvider()
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Provider is invalid MehoAI');
        Settings::setProvider('MehoAI');
    }

    public function testSetAndGetDefaultTimeout()
    {
        Settings::setDefaultTimeout(55);
        self::assertSame(55, Settings::getDefaultTimeout());
    }

    public function testSetAndGetDefaultInputChars()
    {
        Settings::setDefaultInputChars(55);
        self::assertSame(55, Settings::getDefaultInputChars());
    }

    public function testSetAndGetDefaultMaxTokens()
    {
        Settings::setDefaultMaxTokens(55);
        self::assertSame(55, Settings::getDefaultMaxTokens());
    }

    public function testSetAndGetProviderConfig()
    {
        Settings::setProviderConfig(['Meho', 'Muhamed']);
        self::assertSame(['Meho', 'Muhamed'], Settings::getProviderConfig());
    }

    public function testGetDefaultLanguageInitialized()
    {
        // $languages initialized
        Settings::setLanguages(['default' => 'Bosnian', 'Croatian', 'German', 'Dutch']);
        self::assertSame('Bosnian', Settings::getDefaultLanguage());
    }

    public function testGetDefaultLanguageInitializedNoDefault()
    {
        Settings::setLanguages(['Mandarin', 'Croatian', 'German', 'Dutch']);
        self::assertSame('Mandarin', Settings::getDefaultLanguage());
    }

    public function testGetDefaultLanguageNoValue()
    {
        Settings::setLanguages([]);
        self::assertSame('Bosnian', Settings::getDefaultLanguage());
    }

    public function testGetDefaultStyleInitialized()
    {
        Settings::setStyles(['professional', 'default' => 'casual', 'assertive', 'enthusiastic', 'funny', 'informational', 'persuasive']);
        self::assertSame('casual', Settings::getDefaultStyle());
    }

    public function testGetDefaultStyleInitializedNoDefault()
    {
        Settings::setStyles(['professional', 'casual', 'assertive', 'enthusiastic', 'funny', 'informational', 'persuasive']);
        self::assertSame('professional', Settings::getDefaultStyle());
    }

    public function testGetDefaultStyleNoValues()
    {
        Settings::setStyles([]);
        self::assertSame('casual', Settings::getDefaultStyle());
    }

    public function testSetAndGetCreativityValid()
    {
        Settings::setCreativity('low');
        self::assertSame('low', Settings::getCreativity());
    }

    public function testSetAndGetCreativityInalid()
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid creativity value provided.  Valid values are: low, medium, high');
        Settings::setCreativity('mehoCreativity');
    }

    public function testGetDefaultLengthInitialized()
    {
        Settings::setLengths(['short', 'default' => 'medium', 'long']);

        self::assertSame(
            'medium',
            Settings::getDefaultLength()
        );
    }

    public function testGetDefaultLengthInitializedNoDefault()
    {
        Settings::setLengths(['short', 'medium', 'long']);

        self::assertSame('short', Settings::getDefaultLength());
    }

    public function testGetDefaultLengthNoValue()
    {
        Settings::setLengths([]);
        self::assertSame('medium', Settings::getDefaultLength());
    }
}
