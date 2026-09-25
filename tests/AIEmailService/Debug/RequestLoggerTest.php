<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Debug;

use HercegDoo\AIComposePlugin\AIEmailService\Debug\RequestLogger;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\EmailPrompt;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class RequestLoggerTest extends TestCase
{
    public function testDisabledLoggerWritesNothing(): void
    {
        $lines = [];
        $logger = new RequestLogger(false, '42', static function (string $line) use (&$lines): void {
            $lines[] = $line;
        });

        $trace = $logger->begin('OpenAI', 'gpt-4.1', new EmailPrompt('System', 'Private email'));
        $logger->finish($trace, 'success', [], ['total_tokens' => 12]);

        self::assertNull($trace);
        self::assertSame([], $lines);
    }

    public function testRequestAndResultShareAnIdAndRecordPromptsAndUsage(): void
    {
        $lines = [];
        $logger = new RequestLogger(true, '42', static function (string $line) use (&$lines): void {
            $lines[] = json_decode($line, true, 512, \JSON_THROW_ON_ERROR);
        });

        $trace = $logger->begin('OpenAI', 'gpt-4.1', new EmailPrompt('System', "Private\nemail", 'subject'), ['token_limit' => 100]);
        $logger->finish($trace, 'success', ['http_status' => 200], ['prompt_tokens' => 5, 'completion_tokens' => 7, 'total_tokens' => 12]);

        self::assertCount(2, $lines);
        self::assertSame('request', $lines[0]['event']);
        self::assertSame('subject', $lines[0]['operation']);
        self::assertSame("Private\nemail", $lines[0]['prompt']['user']);
        self::assertSame('42', $lines[0]['user_id']);
        self::assertSame('result', $lines[1]['event']);
        self::assertSame($lines[0]['request_id'], $lines[1]['request_id']);
        self::assertSame(12, $lines[1]['usage']['total_tokens']);
        self::assertGreaterThanOrEqual(0, $lines[1]['duration_ms']);
    }

    public function testLoggingFailureDoesNotBreakRequest(): void
    {
        $logger = new RequestLogger(true, '42', static function (): void {
            throw new \RuntimeException('Log destination unavailable');
        });

        $trace = $logger->begin('OpenAI', 'gpt-4.1', new EmailPrompt('System', 'User'));
        self::assertNotNull($trace);
        $logger->finish($trace, 'error');
    }
}
