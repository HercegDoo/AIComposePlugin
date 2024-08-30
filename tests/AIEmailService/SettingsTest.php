<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService;

use HercegDoo\AIComposePlugin\AIEmailService\Providers\OpenAI;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use HercegDoo\AIComposePlugin\TestSupport\ReflectionHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SettingsTest extends TestCase
{
    public function testConstruct()
    {
        $OpenAI = new OpenAI();
        $settings = new Settings($OpenAI);

        $reflectionProvider = ReflectionHelper::getPrivateProperty($settings, 'provider');

        self::assertSame($OpenAI, $reflectionProvider);
    }

    public function testGetProvider()
    {
        $OpenAI = new OpenAI();
        $settings = new Settings($OpenAI);

        self::assertSame($OpenAI, $settings->getProvider());
    }

    public function testGetStyles()
    {
        self::assertSame(['casual', 'professional'], Settings::getStyles());
    }

    public function testGetCreativities()
    {
        self::assertSame(['low', 'medium', 'high'], Settings::getCreativities());
    }

    public function testGetLanguages()
    {
        self::assertSame(['English', 'Spanish', 'Bosnian'], Settings::getLanguages());
    }

    public function testGetLengths()
    {
        self::assertSame(['short', 'medium', 'long'], Settings::getLengths());
    }
}
