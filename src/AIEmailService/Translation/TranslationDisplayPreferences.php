<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Translation;

use HercegDoo\AIComposePlugin\AIEmailService\Summary\SummaryDisplayPreferences;

final class TranslationDisplayPreferences
{
    public const MESSAGE = 'translationMessage';

    /** @param array<string, mixed> $defaults */
    public static function isEnabled(array $defaults): bool
    {
        return SummaryDisplayPreferences::choice($defaults, self::MESSAGE) === SummaryDisplayPreferences::SHOW;
    }
}
