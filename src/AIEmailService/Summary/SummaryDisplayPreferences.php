<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Summary;

final class SummaryDisplayPreferences
{
    public const HOVER = 'summaryHover';
    public const MESSAGE = 'summaryMessage';
    public const SHOW = 'show';
    public const HIDE = 'hide';

    /**
     * @param array<string, mixed> $defaults
     */
    public static function choice(array $defaults, string $preference): string
    {
        $value = $defaults[$preference] ?? null;

        return \is_string($value) && self::isValid($value)
            ? $value
            : ($preference === self::HOVER ? self::HIDE : self::SHOW);
    }

    public static function isValid(string $value): bool
    {
        return \in_array($value, [self::SHOW, self::HIDE], true);
    }

    /**
     * @param array<string, mixed> $defaults
     */
    public static function isEnabled(array $defaults, string $view): bool
    {
        if ($view !== 'preview' && $view !== 'message') {
            return false;
        }

        $preference = $view === 'preview' ? self::HOVER : self::MESSAGE;

        return self::choice($defaults, $preference) === self::SHOW;
    }
}
