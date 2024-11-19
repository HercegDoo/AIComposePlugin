<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tasks;

use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use HercegDoo\AIComposePlugin\Utilities\ContentInjecter;
use HercegDoo\AIComposePlugin\Utilities\TemplateObjectFiller;

class MailTask extends AbstractTask
{
    public function init(): void
    {
        $this->plugin->add_hook('startup', [$this, 'startup']);
        $this->plugin->add_hook('render_page', [$this, 'load_resources']);
        $this->plugin->add_hook('render_page', [$this, 'add_instruction_field']);
        $this->plugin->add_hook('render_page', [$this, 'add_form_buttons']);
        $this->plugin->add_hook('render_page', [$this, 'add_select_fields']);
        $this->plugin->add_hook('render_page', [$this, 'add_help_examples']);
        $this->plugin->add_hook('render_page', [$this, 'create_predefined_instructions_template']);
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

        $aiComposeInstructionField = '';
        $htmlFilePath = __DIR__ . '/../../skins/elastic/templates/aicomposeinstructionfield.html';
        if (file_exists($htmlFilePath)) {
            $aiComposeInstructionField = file_get_contents($htmlFilePath);
        }

        $test = \rcmail::get_instance()->output->just_parse($aiComposeInstructionField);

        $contentInjector = new ContentInjecter();

        return $contentInjector->insertContentAboveElement($args, $test, 'composebodycontainer');
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function add_form_buttons(array $args): array
    {
        $aiComposeButtons = '';
        $htmlFilePath = __DIR__ . '/../../skins/elastic/templates/buttons.html';
        if (file_exists($htmlFilePath)) {
            $aiComposeButtons = file_get_contents($htmlFilePath);
        }

        $test = \rcmail::get_instance()->output->just_parse($aiComposeButtons);

        $contentInjector = new ContentInjecter();

        return $contentInjector->insertContentAboveElement($args, $test, 'formbuttons');
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function add_select_fields(array $args): array
    {
        $contentInjector = new ContentInjecter();

        $aiSelectFields = '';
        $htmlFilePath = __DIR__ . '/../../skins/elastic/templates/aiselectfields.html';
        if (file_exists($htmlFilePath)) {
            $aiSelectFields = file_get_contents($htmlFilePath);
        }
        $test = \rcmail::get_instance()->output->just_parse($aiSelectFields);

        return $contentInjector->insertContentAboveElement($args, $test, 'compose-attachments');
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function add_help_examples(array $args): array
    {
        $contentInjector = new ContentInjecter();

        $helpExamplesModal = '';
        $htmlFilePath = __DIR__ . '/../../skins/elastic/templates/instructionexamples.html';
        if (file_exists($htmlFilePath)) {
            $helpExamplesModal = file_get_contents($htmlFilePath);
        }
        $parsedHelpExamplesModal = \rcmail::get_instance()->output->just_parse($helpExamplesModal);

        return $contentInjector->insertContentAboveElement($args, $parsedHelpExamplesModal, 'layout-content');
    }

    public function style_select_create(): string
    {
        $objectFiller = new TemplateObjectFiller();

        return $objectFiller->createSelectField('styles', 'style_select');
    }

    public function length_select_create(): string
    {
        $objectFiller = new TemplateObjectFiller();

        return $objectFiller->createSelectField('lengths', 'length_select');
    }

    public function creativity_select_create(): string
    {
        $objectFiller = new TemplateObjectFiller();

        return $objectFiller->createSelectField('creativities', 'creativity_select');
    }

    public function language_select_create(): string
    {
        $objectFiller = new TemplateObjectFiller();

        return $objectFiller->createSelectField('languages', 'language_select');
    }

    public function create_instruction_field(): string
    {
        $objectFiller = new TemplateObjectFiller();

        return $objectFiller->createInstructionField();
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function create_predefined_instructions_template(array $args): array
    {
        $predefinedInstructionsTemplate = '';
        $htmlFilePath = __DIR__ . '/../../skins/elastic/templates/popup.html';
        if (file_exists($htmlFilePath)) {
            $predefinedInstructionsTemplate = file_get_contents($htmlFilePath);
        }

        $parsedPredefinedInstructionsTemplate = \rcmail::get_instance()->output->just_parse($predefinedInstructionsTemplate);

        $contentInjector = new ContentInjecter();

        return $contentInjector->insertContentAboveElement($args, $parsedPredefinedInstructionsTemplate, 'headers-menu');
    }

    public function create_instruction_dropdown(): string
    {
        $templateFiller = new TemplateObjectFiller();

        return $templateFiller->fillPredefinedInstructions();
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
