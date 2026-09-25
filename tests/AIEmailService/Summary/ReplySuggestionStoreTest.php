<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Summary;

use HercegDoo\AIComposePlugin\Utilities\ReplySuggestionStore;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ReplySuggestionStoreTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SESSION['aicompose_reply_suggestions']);
    }

    public function testSuggestionOnlyOpensMatchingReplyOnce(): void
    {
        $token = ReplySuggestionStore::save('123', 'INBOX', 'Agree to proceed.', 'English');

        self::assertNull(ReplySuggestionStore::consume($token, '124', 'INBOX'));
        self::assertNull(ReplySuggestionStore::consume($token, '123', 'Archive'));
        self::assertSame(
            ['instruction' => 'Agree to proceed.', 'language' => 'English'],
            ReplySuggestionStore::consume($token, '123', 'INBOX')
        );
        self::assertNull(ReplySuggestionStore::consume($token, '123', 'INBOX'));
    }

    public function testExpiredSuggestionCannotBeUsed(): void
    {
        $token = ReplySuggestionStore::save('123', 'INBOX', 'Agree to proceed.', 'English');
        $_SESSION['aicompose_reply_suggestions'][$token]['created'] = time() - 301;

        self::assertNull(ReplySuggestionStore::consume($token, '123', 'INBOX'));
    }
}
