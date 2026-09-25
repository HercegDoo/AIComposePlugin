<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService;

use HercegDoo\AIComposePlugin\Actions\Mail\SaveComposeOptionsAction;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;
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
final class SaveComposeOptionsActionTest extends TestCase
{
    private $originalInstance;

    /** @var array<string, mixed> */
    private array $originalPost;

    private \ReflectionProperty $instanceProperty;
    private ComposeOptionsUser $user;

    protected function setUp(): void
    {
        $this->originalPost = $_POST;
        $this->instanceProperty = new \ReflectionProperty(\rcube::class, 'instance');
        $this->instanceProperty->setAccessible(true);
        $this->originalInstance = $this->instanceProperty->getValue();

        $mail = new ComposeOptionsRcmail();
        $this->user = new ComposeOptionsUser();
        $mail->user = $this->user;
        $this->instanceProperty->setValue($mail);

        Settings::setStyles(['professional', 'casual']);
        Settings::setLengths(['short', 'medium', 'long']);
        Settings::setLanguages(['Bosnian', 'English']);
    }

    protected function tearDown(): void
    {
        $_POST = $this->originalPost;
        $this->instanceProperty->setValue($this->originalInstance);
    }

    public function testButtonRequestSavesAllChoicesAndPreservesOtherPreferences(): void
    {
        $this->user->prefs = ['aicDefaults' => ['summaryHover' => 'hide']];
        $_POST['data']['aic'] = [
            'style' => 'casual',
            'length' => 'long',
            'creativity' => 'high',
            'language' => 'english',
        ];

        self::assertSame(['status' => 'success'], $this->runAction());
        self::assertSame([
            'summaryHover' => 'hide',
            'style' => 'casual',
            'length' => 'long',
            'creativity' => 'high',
            'language' => 'English',
        ], $this->user->get_prefs()['aicDefaults']);
        self::assertSame('casual', Settings::getDefaultStyle());
        self::assertSame('long', Settings::getDefaultLength());
        self::assertSame('high', Settings::getCreativity());
        self::assertSame('English', Settings::getDefaultLanguage());
    }

    public function testInvalidChoiceDoesNotSaveAnyPreferences(): void
    {
        $_POST['data']['aic'] = [
            'style' => 'casual',
            'length' => 'unsupported',
            'creativity' => 'high',
            'language' => 'english',
        ];

        self::assertSame(['status' => 'error'], $this->runAction());
        self::assertSame([], $this->user->get_prefs());
    }

    /** @return array<string, string> */
    private function runAction(): array
    {
        $action = new SaveComposeOptionsAction();
        $handler = new \ReflectionMethod($action, 'handler');
        $handler->setAccessible(true);

        ob_start();
        try {
            $handler->invoke($action);

            return json_decode((string) ob_get_contents(), true, 512, \JSON_THROW_ON_ERROR);
        } finally {
            ob_end_clean();
        }
    }
}

final class ComposeOptionsRcmail extends \rcmail
{
    public function __construct()
    {
    }
}

final class ComposeOptionsUser
{
    /** @var array<string, mixed> */
    public array $prefs = [];

    /** @return array<string, mixed> */
    public function get_prefs(): array
    {
        return $this->prefs;
    }

    /** @param array<string, mixed> $prefs */
    public function save_prefs(array $prefs): bool
    {
        $this->prefs = array_merge($this->prefs, $prefs);

        return true;
    }
}
