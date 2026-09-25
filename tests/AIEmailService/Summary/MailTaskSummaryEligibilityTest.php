<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Summary;

use HercegDoo\AIComposePlugin\Tasks\MailTask;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 *
 * @runTestsInSeparateProcesses
 *
 * @preserveGlobalState disabled
 */
final class MailTaskSummaryEligibilityTest extends TestCase
{
    public function testOpenedMessageEligibilityIsAvailableBeforeRendering(): void
    {
        $instance = new \ReflectionProperty(\rcube::class, 'instance');
        $instance->setAccessible(true);
        $original = $instance->getValue();
        $originalGet = $_GET;

        $mail = new SummaryEligibilityRcmail();
        $mail->action = 'show';
        $mail->config = new SummaryEligibilityConfig();
        $mail->user = new SummaryEligibilityUser();
        $mail->output = new SummaryEligibilityOutput();
        $instance->setValue($mail);
        $_GET['_uid'] = '42';

        try {
            $task = (new \ReflectionClass(MailTask::class))->newInstanceWithoutConstructor();
            $message = $this->getMockBuilder(\rcube_message::class)->disableOriginalConstructor()->getMock();
            $message->uid = '42';
            $message->headers = (object) [];
            $message->parts = [];
            $args = ['object' => $message];

            $message->body = str_repeat('word ', 149);
            self::assertSame($args, $task->setSummaryEligibility($args));
            self::assertFalse($mail->output->env['aiSummaryAutoEligible']);

            $message->body = str_repeat('word ', 150);
            $task->setSummaryEligibility($args);
            self::assertTrue($mail->output->env['aiSummaryAutoEligible']);

            $mail->user->summaryMessage = 'show';
            $message->body = 'short';
            $task->setSummaryEligibility($args);
            self::assertTrue($mail->output->env['aiSummaryAutoEligible']);

            $mail->user->summaryMessage = 'hide';
            $mail->output->env = [];
            $task->setSummaryEligibility($args);
            self::assertArrayNotHasKey('aiSummaryAutoEligible', $mail->output->env);

            $mail->user->summaryMessage = 'long';
            $_GET['_uid'] = '43';
            $task->setSummaryEligibility($args);
            self::assertArrayNotHasKey('aiSummaryAutoEligible', $mail->output->env);
        } finally {
            $_GET = $originalGet;
            $instance->setValue($original);
        }
    }
}

final class SummaryEligibilityRcmail extends \rcmail
{
    public function __construct()
    {
    }
}

final class SummaryEligibilityConfig
{
    public function get(string $key, $default = null)
    {
        return $key === 'aiSummaryEnabled' ? true : $default;
    }
}

final class SummaryEligibilityUser
{
    public string $summaryMessage = 'long';

    /** @return array<string, array<string, string>> */
    public function get_prefs(): array
    {
        return ['aicDefaults' => ['summaryMessage' => $this->summaryMessage]];
    }
}

final class SummaryEligibilityOutput
{
    /** @var array<string, bool> */
    public array $env = [];

    public function get_charset(): string
    {
        return 'UTF-8';
    }

    public function set_env(string $key, bool $value): void
    {
        $this->env[$key] = $value;
    }
}
