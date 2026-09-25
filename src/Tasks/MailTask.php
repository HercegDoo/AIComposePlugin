<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tasks;

use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use HercegDoo\AIComposePlugin\AIEmailService\Summary\SummaryDisplayPreferences;
use HercegDoo\AIComposePlugin\Utilities\ContentInjector;
use HercegDoo\AIComposePlugin\Utilities\ReplySuggestionStore;
use HercegDoo\AIComposePlugin\Utilities\TemplateObjectFiller;

class MailTask extends AbstractTask
{
    private ContentInjector $contentInjector;
    private TemplateObjectFiller $templateObjectFiller;

    public function init(): void
    {
        if (!$this->isPluginVisible()) {
            return;
        }
        $this->contentInjector = ContentInjector::getContentInjector();
        $this->templateObjectFiller = TemplateObjectFiller::getTemplateObjectFiller();

        $this->plugin->add_hook('startup', [$this, 'startup']);
        $this->plugin->add_hook('render_page', [$this, 'loadResources']);
        $this->plugin->add_hook('render_page', [$this, 'attachSuggestedReply']);
        $this->plugin->add_hook('render_page', [$this, 'addInstructionField']);
        $this->plugin->add_hook('render_page', [$this, 'addSelectFields']);
        $this->plugin->add_hook('render_page', [$this, 'addHelpExamples']);
        $this->plugin->add_hook('render_page', [$this, 'createPredefinedInstructionsTemplate']);
        $this->plugin->add_hook('render_page', [$this, 'addTooltip']);
        $this->plugin->add_hook('preferences_save', [$this, 'preferencesSave']);
        \rcmail::get_instance()->output->add_handlers(
            [
                'aistyleselect' => [$this, 'style_select_create'],
                'ailengthselect' => [$this, 'length_select_create'],
                'aicreativityselect' => [$this, 'creativity_select_create'],
                'ailanguageselect' => [$this, 'language_select_create'],
                'aicinstruction' => [$this, 'create_instruction_field'],
                'aicinstructiondropdown' => [$this, 'create_instruction_dropdown'],
                'showinstructionsbutton' => [$this, 'create_show_instructions_button'],
                'aicfixinstruction' => [$this, 'create_fix_text_instruction']]
        );
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function loadResources(array $args): array
    {
        if (($args['template'] ?? null) === 'compose') {
            $this->includeComposeScripts();
        }
        $summaryViews = $this->summaryViews();
        $template = $args['template'] ?? null;
        if (($this->summaryEnabled() && (($template === 'mail' && $summaryViews['preview'])
            || ($template === 'message' && $summaryViews['message'])))
            || ($template === 'message' && $this->translationEnabled())) {
            $this->includeBundle('summary');
        }

        return $args;
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function attachSuggestedReply(array $args): array
    {
        if (($args['template'] ?? null) !== 'compose') {
            return $args;
        }

        $composeId = \rcube_utils::get_input_string('_id', \rcube_utils::INPUT_GET);
        $compose = $_SESSION['compose_data_' . $composeId] ?? null;
        $params = \is_array($compose) ? ($compose['param'] ?? null) : null;
        $token = \is_array($params) ? ($params['aic_reply_token'] ?? null) : null;
        $uid = \is_array($params) ? ($params['reply_uid'] ?? null) : null;
        $mailbox = \is_array($compose) ? ($compose['mailbox'] ?? null) : null;
        if (!\is_string($token) || !preg_match('/^[a-f0-9]{32}$/', $token)
            || !\is_string($uid) || !\is_string($mailbox)) {
            return $args;
        }

        $suggestion = ReplySuggestionStore::consume($token, $uid, $mailbox);
        if ($suggestion !== null) {
            \rcmail::get_instance()->output->set_env('aiReplySuggestion', $suggestion);
        }

        return $args;
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function addInstructionField(array $args): array
    {
        return $this->contentInjector->insertContent($args, 'composebodycontainer', 'ai_compose_instruction_field', 'prepend');
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function addTooltip(array $args): array
    {
        return $this->contentInjector->insertContent($args, 'headers-menu', 'fix_text_tootltip', 'prepend');
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function addSelectFields(array $args): array
    {
        return $this->contentInjector->insertContent($args, 'compose-options', 'ai_select_fields');
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function addHelpExamples(array $args): array
    {
        return $this->contentInjector->insertContent($args, 'layout-content', 'instruction_examples', 'prepend');
    }

    public function style_select_create(): string
    {
        return $this->templateObjectFiller->createSelectField('styles', 'style_select');
    }

    public function length_select_create(): string
    {
        return $this->templateObjectFiller->createSelectField('lengths', 'length_select');
    }

    public function creativity_select_create(): string
    {
        return $this->templateObjectFiller->createSelectField('creativities', 'creativity_select');
    }

    public function language_select_create(): string
    {
        return $this->templateObjectFiller->createSelectField('languages', 'language_select');
    }

    public function create_instruction_field(): string
    {
        return $this->templateObjectFiller->createInstructionField('aicinstruction', 'aic-instruction');
    }

    public function create_fix_text_instruction(): string
    {
        return $this->templateObjectFiller->createInstructionField('aicfixinstruction', 'fix-text-aic-instruction');
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function createPredefinedInstructionsTemplate(array $args): array
    {
        return $this->contentInjector->insertContent($args, 'headers-menu', 'popup', 'prepend');
    }

    public function create_instruction_dropdown(): string
    {
        return $this->templateObjectFiller->fillPredefinedInstructions();
    }

    public function create_show_instructions_button(): string
    {
        return $this->templateObjectFiller->fillButton('aic-example-instructions', 'ai_button_show_instructions');
    }

    public function startup(): void
    {
        $actionPrefix = 'plugin.aicomposeplugin_';
        $rcmail = \rcmail::get_instance();
        $settings = [
            'languages' => array_values(Settings::getLanguages()),
            'defaultLanguage' => Settings::getDefaultLanguage(),
            'lengths' => array_values(Settings::getLengths()),
            'defaultLength' => Settings::getDefaultLength(),
            'creativities' => array_values(Settings::getCreativities()),
            'defaultCreativity' => Settings::getCreativity(),
            'styles' => array_values(Settings::getStyles()),
            'defaultStyle' => Settings::getDefaultStyle(),
        ];

        if ($rcmail->action === 'compose' || str_starts_with($rcmail->action, $actionPrefix)) {
            $this->loadTranslations();
            $rcmail->output->set_env('aiPluginOptions', $settings);
            $rcmail->output->set_env('aiPredefinedInstructions', $rcmail->user->get_prefs()['predefinedInstructions'] ?? []);
            if ($rcmail->action === 'compose') {
                $this->includeComposeScripts();
            }
        } elseif ($this->summaryEnabled() || $this->translationEnabled()) {
            $this->loadTranslations();
            $rcmail->output->set_env('aiSummaryViews', $this->summaryViews());
            $rcmail->output->set_env('aiSummaryEnabled', $this->summaryEnabled());
            $rcmail->output->set_env('aiTranslationEnabled', $this->translationEnabled());
            if ($this->translationEnabled()) {
                $languages = $rcmail->list_languages();
                $locale = $_SESSION['language'] ?? $rcmail->config->get('language', 'en_US');
                $defaults = $rcmail->user->get_prefs()['aicDefaults'] ?? [];
                $choice = \is_array($defaults) ? ($defaults['summaryLanguage'] ?? null) : null;
                if (\is_string($choice) && isset($languages[$choice])) {
                    $locale = $choice;
                }
                if (!\is_string($locale) || !isset($languages[$locale])) {
                    $locale = array_key_first($languages);
                }
                $rcmail->output->set_env('aiTranslationLanguages', $languages);
                $rcmail->output->set_env('aiTranslationDefaultLocale', $locale);
            }
        }
    }

    private function includeComposeScripts(): void
    {
        foreach (['composeOptions', 'compose'] as $name) {
            $this->includeBundle($name);
        }
    }

    private function includeBundle(string $name): void
    {
        $bundle = 'assets/dist/' . $name . '.bundle.js';
        $bundlePath = __DIR__ . '/../../' . $bundle;
        $hash = is_file($bundlePath) ? hash_file('sha256', $bundlePath) : false;
        $this->plugin->include_script($bundle . ($hash ? '?v=' . substr($hash, 0, 12) : ''));
    }

    private function summaryEnabled(): bool
    {
        return (bool) \rcmail::get_instance()->config->get('aiSummaryEnabled', true);
    }

    private function translationEnabled(): bool
    {
        return (bool) \rcmail::get_instance()->config->get('aiTranslationEnabled', true);
    }

    /**
     * @return array{preview: bool, message: bool}
     */
    private function summaryViews(): array
    {
        $defaults = \rcmail::get_instance()->user->get_prefs()['aicDefaults'] ?? [];
        if (!\is_array($defaults)) {
            $defaults = [];
        }

        return [
            'preview' => SummaryDisplayPreferences::isEnabled($defaults, 'preview'),
            'message' => SummaryDisplayPreferences::isEnabled($defaults, 'message'),
        ];
    }

    private function isPluginVisible(): bool
    {
        $pluginVisibility = \rcmail::get_instance()->user->get_prefs()['aicDefaults']['pluginVisibility'] ?? 'show';

        return $pluginVisibility === 'show';
    }
}
