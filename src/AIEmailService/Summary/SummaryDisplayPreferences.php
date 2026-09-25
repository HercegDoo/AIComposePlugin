<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Summary;

final class SummaryDisplayPreferences
{
    public const HOVER = 'summaryHover';
    public const MESSAGE = 'summaryMessage';
    public const SHOW = 'show';
    public const HIDE = 'hide';
    public const LONG = 'long';

    private const DEFAULTS = [
        self::HOVER => self::HIDE,
        self::MESSAGE => self::LONG,
    ];

    /**
     * @param array<string, mixed> $defaults
     */
    public static function choice(array $defaults, string $preference): string
    {
        $value = $defaults[$preference] ?? null;

        return \is_string($value) && self::isValidFor($value, $preference)
            ? $value
            : (self::DEFAULTS[$preference] ?? self::SHOW);
    }

    public static function isValid(string $value): bool
    {
        return \in_array($value, [self::SHOW, self::HIDE], true);
    }

    public static function isValidFor(string $value, string $preference): bool
    {
        return self::isValid($value) || ($preference === self::MESSAGE && $value === self::LONG);
    }

    /** @param array<string, mixed> $defaults */
    public static function shouldSummarizeMessage(array $defaults, string $body): bool
    {
        $choice = self::choice($defaults, self::MESSAGE);

        return $choice === self::SHOW || ($choice === self::LONG && SummaryPromptBuilder::isLongMessage($body));
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

        return self::choice($defaults, $preference) !== self::HIDE;
    }
}
