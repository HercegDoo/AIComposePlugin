<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService;

include 'config.inc.php';

use HercegDoo\AIComposePlugin\AIEmailService\Providers\InterfaceProvider;

final class Settings
{
    public static int $default_timeout;
    public static int $default_input_chars;
    public static int $default_max_tokens;

    public static string $STYLE_CASUAL;
    public static string $STYLE_PROFESSIONAL;
    public static string $STYLE_ENTHUSIASTIC;
    public static string $STYLE_FUNNY;
    public static string $STYLE_INFORMATIONAL;
    public static string $STYLE_PERSUASIVE;

    public static string $STYLE_ASSERTIVE;
    public static string $LENGTH_SHORT;
    public static string $LENGTH_MEDIUM;
    public static string $LENGTH_LONG;
    public static string $CREATIVITY_LOW;
    public static string $CREATIVITY_MEDIUM;
    public static string $CREATIVITY_HIGH;
    public static string $LANGUAGE_ENGLISH;
    public static string $LANGUAGE_CROATIAN;
    public static string $LANGUAGE_BOSNIAN;
    public static string $LANGUAGE_GERMAN;
    public static string $LANGUAGE_DUTCH;

    /**
     * @var array<string, string>
     */
    public array $providerOpenAI = [];

    private static ?Settings $settingsInstance = null;

    private InterfaceProvider $provider;

    private function __construct(InterfaceProvider $provider)
    {
        global $config;
        $this->provider = $provider;

        if ($provider->getProviderName() === 'OpenAI') {
            $this->providerOpenAI = [
                'apiKey' => $config['provider_openai_config']['apiKey'],
                'model' => $config['provider_openai_config']['model'],
            ];
        }
    }

    public static function getSettingsInstance(InterfaceProvider $provider): self
    {
        if (self::$settingsInstance === null) {
            self::$settingsInstance = new self($provider);
        }

        return self::$settingsInstance;
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
