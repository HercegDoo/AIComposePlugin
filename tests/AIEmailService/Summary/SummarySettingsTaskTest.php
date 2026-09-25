<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tests\AIEmailService\Summary;

use HercegDoo\AIComposePlugin\Tasks\SettingsTask;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class SummarySettingsTaskTest extends TestCase
{
    private $originalInstance;

    /** @var array<string, mixed> */
    private array $originalPost;

    private \ReflectionProperty $instanceProperty;
    private SettingsTask $task;
    private SummarySettingsUser $user;

    protected function setUp(): void
    {
        $this->originalPost = $_POST;
        $this->instanceProperty = new \ReflectionProperty(\rcube::class, 'instance');
        $this->instanceProperty->setAccessible(true);
        $this->originalInstance = $this->instanceProperty->getValue();

        $mail = new SummarySettingsRcmail();
        $this->user = new SummarySettingsUser();
        $mail->user = $this->user;
        $this->instanceProperty->setValue($mail);
        $this->task = (new \ReflectionClass(SettingsTask::class))->newInstanceWithoutConstructor();
    }

    protected function tearDown(): void
    {
        $_POST = $this->originalPost;
        $this->instanceProperty->setValue($this->originalInstance);
    }

    public function testSettingsPageShowsSummaryAndTranslationControls(): void
    {
        $this->user->prefs = ['aicDefaults' => ['summaryHover' => 'hide', 'translationMessage' => 'hide']];
        $blocks = $this->task->preferencesList(['section' => 'aic'])['blocks'];
        $options = $blocks['general']['options'];

        self::assertStringContainsString('name="data[aic][summaryHover]"', $options[2]['content']);
        self::assertStringContainsString('<option value="hide" selected>', $options[2]['content']);
        self::assertStringContainsString('name="data[aic][summaryMessage]"', $options[3]['content']);
        self::assertStringContainsString('<option value="show" selected>', $options[3]['content']);
        self::assertStringContainsString('name="data[aic][translationMessage]"', $options[4]['content']);
        self::assertStringContainsString('<option value="hide" selected>', $options[4]['content']);
    }

    public function testSavePreservesOtherDefaultsAndStoresAllVisibilityChoices(): void
    {
        $this->user->prefs = ['aicDefaults' => ['style' => 'casual']];
        $_POST['data']['aic'] = [
            'pluginVisibility' => 'show',
            'summaryLanguage' => 'roundcube',
            'summaryHover' => 'hide',
            'summaryMessage' => 'show',
            'translationMessage' => 'hide',
        ];

        $result = $this->task->preferencesSave(['section' => 'aic', 'prefs' => []]);

        self::assertSame([
            'style' => 'casual',
            'pluginVisibility' => 'show',
            'summaryLanguage' => 'roundcube',
            'summaryHover' => 'hide',
            'summaryMessage' => 'show',
            'translationMessage' => 'hide',
        ], $result['prefs']['aicDefaults']);
    }

    public function testExistingUsersDefaultToShowingTranslation(): void
    {
        $options = $this->task->preferencesList(['section' => 'aic'])['blocks']['general']['options'];

        self::assertStringContainsString('<option value="show" selected>', $options[4]['content']);
    }

    public function testSaveWithoutNewFieldPreservesAnExistingTranslationChoice(): void
    {
        $this->user->prefs = ['aicDefaults' => ['translationMessage' => 'hide']];
        $_POST['data']['aic'] = [
            'pluginVisibility' => 'show',
            'summaryLanguage' => 'roundcube',
        ];

        $result = $this->task->preferencesSave(['section' => 'aic', 'prefs' => []]);

        self::assertSame('hide', $result['prefs']['aicDefaults']['translationMessage']);
    }

    public function testInvalidTranslationVisibilityAbortsSave(): void
    {
        $_POST['data']['aic'] = [
            'pluginVisibility' => 'show',
            'summaryLanguage' => 'roundcube',
            'translationMessage' => 'invalid',
        ];

        $result = $this->task->preferencesSave(['section' => 'aic', 'prefs' => []]);

        self::assertTrue($result['abort']);
        self::assertFalse($result['result']);
    }

    public function testInvalidVisibilityValueAbortsSave(): void
    {
        $_POST['data']['aic'] = [
            'pluginVisibility' => 'show',
            'summaryLanguage' => 'roundcube',
            'summaryHover' => 'invalid',
            'summaryMessage' => 'show',
        ];

        $result = $this->task->preferencesSave(['section' => 'aic', 'prefs' => []]);

        self::assertTrue($result['abort']);
        self::assertFalse($result['result']);
    }
}

final class SummarySettingsRcmail extends \rcmail
{
    public function __construct()
    {
    }

    public function gettext($attrib, $domain = null)
    {
        return $attrib;
    }

    public function list_languages()
    {
        return ['en_US' => 'English'];
    }
}

final class SummarySettingsUser
{
    /** @var array<string, mixed> */
    public array $prefs = [];

    /**
     * @return array<string, mixed>
     */
    public function get_prefs(): array
    {
        return $this->prefs;
    }
}
