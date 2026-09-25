<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Translation;

use HercegDoo\AIComposePlugin\Actions\Mail\TranslateMessageAction;
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
final class TranslateMessageActionTest extends TestCase
{
    public function testHiddenTranslationIsRejectedBeforeReadingMail(): void
    {
        $instance = new \ReflectionProperty(\rcube::class, 'instance');
        $instance->setAccessible(true);
        $original = $instance->getValue();
        $mail = new TranslationActionRcmail();
        $mail->user = new TranslationActionUser();
        $mail->config = new TranslationActionConfig();
        $instance->setValue($mail);

        try {
            $action = new TranslateMessageAction();
            $handler = new \ReflectionMethod($action, 'handler');
            $handler->setAccessible(true);
            ob_start();
            try {
                $handler->invoke($action);
                self::assertSame(['status' => 'error'], json_decode((string) ob_get_contents(), true));
            } finally {
                ob_end_clean();
            }
        } finally {
            $instance->setValue($original);
        }
    }

    public function testMailTaskUsesBothUserAndAdministratorSwitches(): void
    {
        $instance = new \ReflectionProperty(\rcube::class, 'instance');
        $instance->setAccessible(true);
        $original = $instance->getValue();
        $mail = new TranslationActionRcmail();
        $mail->user = new TranslationActionUser();
        $mail->config = new TranslationActionConfig();
        $instance->setValue($mail);

        try {
            $task = (new \ReflectionClass(MailTask::class))->newInstanceWithoutConstructor();
            $enabled = new \ReflectionMethod($task, 'translationEnabled');
            $enabled->setAccessible(true);
            self::assertFalse($enabled->invoke($task));

            $mail->user->choice = 'show';
            self::assertTrue($enabled->invoke($task));

            $mail->config->enabled = false;
            self::assertFalse($enabled->invoke($task));
        } finally {
            $instance->setValue($original);
        }
    }
}

final class TranslationActionRcmail extends \rcmail
{
    public function __construct()
    {
    }
}

final class TranslationActionUser
{
    public string $choice = 'hide';

    /** @return array<string, array<string, string>> */
    public function get_prefs(): array
    {
        return ['aicDefaults' => ['translationMessage' => $this->choice]];
    }
}

final class TranslationActionConfig
{
    public bool $enabled = true;

    public function get(string $key, bool $default): bool
    {
        return $key === 'aiTranslationEnabled' ? $this->enabled : $default;
    }
}
