<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService;

use HercegDoo\AIComposePlugin\AIEmailService\Providers\InterfaceProvider;

final class Settings
{
    public const DEFAULT_TIMEOUT = 60;
    public const DEFAULT_INPUT_CHARS = 500;
    public const DEFAULT_MAX_TOKENS = 2000;
    public const STYLE_CASUAL = 'casual';
    public const STYLE_PROFESSIONAL = 'professional';
    public const LENGTH_SHORT = 'short';
    public const LENGTH_MEDIUM = 'medium';
    public const LENGTH_LONG = 'long';
    public const CREATIVITY_LOW = 'low';
    public const CREATIVITY_MEDIUM = 'medium';
    public const CREATIVITY_HIGH = 'high';
    public const LANGUAGE_ENGLISH = 'English';
    public const LANGUAGE_SPANISH = 'Spanish';
    public const LANGUAGE_BOSNIAN = 'Bosnian';

    /**
     * @var array<string, string>
     */
    public array $providerOpenAI = [
        'apiKey' => '',
        'model' => '',
    ];

    private InterfaceProvider $provider;

    public function __construct(InterfaceProvider $provider)
    {
        $this->provider = $provider;
    }

    public function getProvider(): InterfaceProvider
    {
        return $this->provider;
    }

    /**
     * @return array<int,mixed>
     */
    public static function getStyles(): array
    {
        return array_values(array_filter(
            (new \ReflectionClass(self::class))->getConstants(),
            static fn (string $key) => str_starts_with($key, 'STYLE_'),
            \ARRAY_FILTER_USE_KEY
        ));
    }

    /**
     * @return array<int,mixed>
     */
    public static function getLengths(): array
    {
        return array_values(array_filter(
            (new \ReflectionClass(self::class))->getConstants(),
            static fn (string $key) => str_starts_with($key, 'LENGTH_'),
            \ARRAY_FILTER_USE_KEY
        ));
    }

    /**
     * @return array<int,mixed>
     */
    public static function getCreativities(): array
    {
        return array_values(array_filter(
            (new \ReflectionClass(self::class))->getConstants(),
            static fn (string $key) => str_starts_with($key, 'CREATIVITY_'),
            \ARRAY_FILTER_USE_KEY
        ));
    }

    /**
     * @return array<int,mixed>
     */
    public static function getLanguages(): array
    {
        return array_values(array_filter(
            (new \ReflectionClass(self::class))->getConstants(),
            static fn (string $key) => str_starts_with($key, 'LANGUAGE_'),
            \ARRAY_FILTER_USE_KEY
        ));
    }
}
