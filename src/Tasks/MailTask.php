<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tasks;

use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use HercegDoo\AIComposePlugin\Utilities\ContentInjector;
use HercegDoo\AIComposePlugin\Utilities\TemplateObjectFiller;

class MailTask extends AbstractTask
{
    private ContentInjector $contentInjector;
    private TemplateObjectFiller $templateObjectFiller;

    public function init(): void
    {
        $this->contentInjector = ContentInjector::getContentInjector();
        $this->templateObjectFiller = TemplateObjectFiller::getTemplateObjectFiller();

        $this->plugin->add_hook('startup', [$this, 'startup']);
        $this->plugin->add_hook('render_page', [$this, 'load_resources']);
        $this->plugin->add_hook('render_page', [$this, 'add_instruction_field']);
        $this->plugin->add_hook('render_page', [$this, 'add_form_buttons']);
        $this->plugin->add_hook('render_page', [$this, 'add_select_fields']);
        $this->plugin->add_hook('render_page', [$this, 'add_help_examples']);
        $this->plugin->add_hook('render_page', [$this, 'create_predefined_instructions_template']);
        $this->plugin->add_hook('render_page', [$this, 'add_tooltip']);
        $this->plugin->add_hook('preferences_save', [$this, 'preferencesSave']);
        \rcmail::get_instance()->output->add_handlers(
            [
                'aistyleselect' => [$this, 'style_select_create'],
                'ailengthselect' => [$this, 'length_select_create'],
                'aicreativityselect' => [$this, 'creativity_select_create'],
                'ailanguageselect' => [$this, 'language_select_create'],
                'aicinstruction' => [$this, 'create_instruction_field'],
                'aicinstructiondropdown' => [$this, 'create_instruction_dropdown']]
        );
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function load_resources(array $args): array
    {
        if (isset($args['template']) && $args['template'] == 'compose') {
            $this->plugin->include_script('assets/dist/compose.bundle.js');
        }

        return $args;
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function add_instruction_field(array $args): array
    {
        $this->loadTranslations();

        $parsedHtmlContent = $this->contentInjector->getParsedHtml('ai_compose_instruction_field');

        return $this->contentInjector->insertContentAboveElement($args, $parsedHtmlContent, 'composebodycontainer');
    }

    public function add_tooltip(array $args): array
    {
        $this->loadTranslations();

        $parsedHtmlContent = $this->contentInjector->getParsedHtml('fix_text_tootltip');

        return $this->contentInjector->insertContentAboveElement($args, $parsedHtmlContent, 'composebodycontainer');
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function add_form_buttons(array $args): array
    {
        $parsedHtmlContent = $this->contentInjector->getParsedHtml('buttons');
        $buttonId = $this->contentInjector->findId($parsedHtmlContent)[0];

        if (isset($args['content']) && preg_match('/id=["\']' . $buttonId . '["\']/', \is_string($args['content']) ? $args['content'] : '')) {
            return $args;
        }

        return $this->contentInjector->add_buttons($parsedHtmlContent, 'formbuttons', $args);
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function add_select_fields(array $args): array
    {
        $parsedHtmlContent = $this->contentInjector->getParsedHtml('ai_select_fields');

        return $this->contentInjector->insertContentAboveElement($args, $parsedHtmlContent, 'compose-attachments');
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function add_help_examples(array $args): array
    {
        $parsedHtmlContent = $this->contentInjector->getParsedHtml('instruction_examples');

        return $this->contentInjector->insertContentAboveElement($args, $parsedHtmlContent, 'layout-content');
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
        return $this->templateObjectFiller->createInstructionField();
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function create_predefined_instructions_template(array $args): array
    {
        $parsedHtmlContent = $this->contentInjector->getParsedHtml('popup');

        return $this->contentInjector->insertContentAboveElement($args, $parsedHtmlContent, 'headers-menu');
    }

    public function create_instruction_dropdown(): string
    {
        return $this->templateObjectFiller->fillPredefinedInstructions();
    }

    public function startup(): void
    {
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

        if ($rcmail->action === 'compose') {
            $this->loadTranslations();
            $rcmail->output->set_env('aiPluginOptions', $settings);
            $rcmail->output->set_env('aiPredefinedInstructions', $rcmail->user->get_prefs()['predefinedInstructions'] ?? []);
        }
    }
}
