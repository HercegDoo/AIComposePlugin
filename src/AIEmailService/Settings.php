<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService;

use HercegDoo\AIComposePlugin\AIEmailService\Providers\InterfaceProvider;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\OpenAI;

final class Settings
{
    private static int $defaultTimeout;
    private static int $defaultInputChars;
    private static int $defaultMaxTokens;

    /** @var string[] */
    private static array $creativities = [
        'low',
        'medium',
        'high',
    ];

    /** @var string[] */
    private static array $styles;
    /** @var string[] */
    private static array $lengths;

    private static string $creativity = 'medium';
    /** @var string[] */
    private static array $languages;

    /**
     * @var array<string, string>
     */
    private static array $providerConfig = [];

    private static InterfaceProvider $provider;

    public static function getProvider(): InterfaceProvider
    {
        return self::$provider;
    }

    /**
     * @return array<string>
     */
    public static function getStyles(): array
    {
        return self::$styles;
    }

    /**
     * @return array<string>
     */
    public static function getLengths(): array
    {
        return self::$lengths;
    }

    /**
     * @return array<string>
     */
    public static function getCreativities(): array
    {
        return self::$creativities;
    }

    public static function getDefaultLength(): string
    {
        return self::getLengths()['default'] ?? self::getLengths()[0] ?? 'medium';
    }

    public static function getCreativity(): string
    {
        return self::$creativity;
    }

    public static function setCreativity(string $creativity): void
    {
        $creativities = self::getCreativities();
        if (\in_array($creativity, $creativities, true)) {
            self::$creativity = $creativity;
        } else {
            throw new \InvalidArgumentException('Invalid creativity value provided.  Valid values are: ' . implode(', ', $creativities));
        }
    }

    public static function setProvider(string $provider): void
    {
        switch ($provider) {
            case 'OpenAI':
                self::$provider = new OpenAI();
                break;

            default:
                throw new \InvalidArgumentException('Provider is invalid ' . $provider);
        }
    }

    /**
     * @param array<string> $languages
     */
    public static function setLanguages(array $languages): void
    {
        self::$languages = $languages;
    }

    /**
     * @param string[] $lengths
     */
    public static function setLengths(array $lengths): void
    {
        self::$lengths = $lengths;
    }

    /**
     * @param string[] $styles
     */
    public static function setStyles(array $styles): void
    {
        self::$styles = $styles;
    }

    public static function getDefaultStyle(): string
    {
        return self::getStyles()['default'] ?? self::getStyles()[0] ?? 'casual';
    }

    /**
     * @return array<string>
     */
    public static function getLanguages(): array
    {
        return self::$languages;
    }

    public static function getDefaultLanguage(): string
    {
        return self::getLanguages()['default'] ?? self::getLanguages()[0] ?? 'Bosnian';
    }

    public static function getDefaultTimeout(): int
    {
        return self::$defaultTimeout;
    }

    public static function setDefaultTimeout(int $defaultTimeout): void
    {
        self::$defaultTimeout = $defaultTimeout;
    }

    public static function getDefaultInputChars(): int
    {
        return self::$defaultInputChars;
    }

    public static function setDefaultInputChars(int $defaultInputChars): void
    {
        self::$defaultInputChars = $defaultInputChars;
    }

    public static function getDefaultMaxTokens(): int
    {
        return self::$defaultMaxTokens;
    }

    /**
     * @param int $defaultMaxTokens
     */
    public static function setDefaultMaxTokens($defaultMaxTokens): void
    {
        self::$defaultMaxTokens = $defaultMaxTokens;
    }

    /**
     * @return array<string, string>
     */
    public static function getProviderConfig(): array
    {
        return self::$providerConfig;
    }

    /**
     * @param array<string> $providerConfig
     */
    public static function setProviderConfig(array $providerConfig): void
    {
        self::$providerConfig = $providerConfig;
    }
}
