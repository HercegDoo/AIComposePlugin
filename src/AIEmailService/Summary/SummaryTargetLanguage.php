<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Summary;

final class SummaryTargetLanguage
{
    public const ROUNDCUBE = 'roundcube';
    public const ORIGINAL = 'original';

    /**
     * @param array<string, string> $availableLanguages
     */
    public static function isValid(string $choice, array $availableLanguages): bool
    {
        return $choice === self::ROUNDCUBE || $choice === self::ORIGINAL || isset($availableLanguages[$choice]);
    }

    /**
     * @param array<string, string> $availableLanguages
     *
     * @return array{locale: string, translate: bool}
     */
    public static function resolve(string $choice, string $interfaceLocale, array $availableLanguages): array
    {
        if (!self::isValid($choice, $availableLanguages)) {
            $choice = self::ROUNDCUBE;
        }

        return [
            'locale' => isset($availableLanguages[$choice]) ? $choice : $interfaceLocale,
            'translate' => $choice !== self::ORIGINAL,
        ];
    }
}
