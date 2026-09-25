<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Utilities;

final class ReplySuggestionStore
{
    private const SESSION_KEY = 'aicompose_reply_suggestions';
    private const LIFETIME = 300;

    public static function save(string $uid, string $mailbox, string $instruction, string $language): string
    {
        self::prune();
        if (\count($_SESSION[self::SESSION_KEY]) >= 10) {
            unset($_SESSION[self::SESSION_KEY][array_key_first($_SESSION[self::SESSION_KEY])]);
        }
        $token = bin2hex(random_bytes(16));
        $_SESSION[self::SESSION_KEY][$token] = [
            'uid' => $uid,
            'mailbox' => $mailbox,
            'instruction' => $instruction,
            'language' => $language,
            'created' => time(),
        ];

        return $token;
    }

    /**
     * @return null|array{instruction: string, language: string}
     */
    public static function consume(string $token, string $uid, string $mailbox): ?array
    {
        self::prune();
        $item = $_SESSION[self::SESSION_KEY][$token] ?? null;
        if (!\is_array($item) || ($item['uid'] ?? null) !== $uid || ($item['mailbox'] ?? null) !== $mailbox) {
            return null;
        }

        unset($_SESSION[self::SESSION_KEY][$token]);

        return ['instruction' => $item['instruction'], 'language' => $item['language']];
    }

    private static function prune(): void
    {
        if (empty($_SESSION[self::SESSION_KEY]) || !\is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];

            return;
        }

        foreach ($_SESSION[self::SESSION_KEY] as $token => $item) {
            if (!\is_array($item) || !\is_int($item['created'] ?? null) || time() - $item['created'] > self::LIFETIME) {
                unset($_SESSION[self::SESSION_KEY][$token]);
            }
        }
    }
}
