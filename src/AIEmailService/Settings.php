<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService;

use HercegDoo\AIComposePlugin\AIEmailService\Providers\InterfaceProvider;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\OpenAI;

final class Settings
{
    private static int $default_timeout;
    private static int $default_input_chars;
    private static int $default_max_tokens;

    /** @var string[] */
    private static array $styles;
    /** @var string[] */
    private static array $lengths;
    /** @var string[] */
    private static array $creativities;
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

    public static function getDefaultLength(): string
    {
        return self::getLengths()['default'] ?? self::getLengths()[0] ?? 'medium';
    }

    /**
     * @return array<string>
     */
    public static function getCreativities(): array
    {
        return self::$creativities;
    }

    public static function getDefaultCreativity(): string
    {
        return self::getCreativities()['default'] ?? self::getCreativities()[0] ?? 'medium';
    }

    public static function setProvider(string $provider): void
    {
        switch ($provider) {
            case 'openai':
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
     * @param array<string> $creativities
     */
    public static function setCreativities(array $creativities): void
    {
        self::$creativities = $creativities;
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
        return self::$default_timeout;
    }

    public static function setDefaultTimeout(int $default_timeout): void
    {
        self::$default_timeout = $default_timeout;
    }

    public static function getDefaultInputChars(): int
    {
        return self::$default_input_chars;
    }

    public static function setDefaultInputChars(int $default_input_chars): void
    {
        self::$default_input_chars = $default_input_chars;
    }

    public static function getDefaultMaxTokens(): int
    {
        return self::$default_max_tokens;
    }

    /**
     * @param int $default_max_tokens
     */
    public static function setDefaultMaxTokens($default_max_tokens): void
    {
        self::$default_max_tokens = $default_max_tokens;
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
