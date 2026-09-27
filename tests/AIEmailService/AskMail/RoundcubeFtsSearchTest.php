<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\AskMail;

use HercegDoo\AIComposePlugin\AIEmailService\AskMail\RoundcubeFtsSearch;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class RoundcubeFtsSearchTest extends TestCase
{
    public function testCriteriaUsesOnlyQuotedWordsFromQuestion(): void
    {
        self::assertSame('TEXT "password" TEXT "client"', RoundcubeFtsSearch::criteria('What did the client say about password?'));
        self::assertSame('TEXT "password" TEXT "ALL"', RoundcubeFtsSearch::criteria('password" OR ALL'));
        self::assertSame('', RoundcubeFtsSearch::criteria('a?'));
    }
}
