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
        $this->plugin->add_hook('render_page', [$this, 'loadResources']);
        $this->plugin->add_hook('render_page', [$this, 'addInstructionField']);
        $this->plugin->add_hook('render_page', [$this, 'addFormButtons']);
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
    public function addInstructionField(array $args): array
    {
        return $this->contentInjector->insertContentAboveElement($args, 'ai_compose_instruction_field', '#composebodycontainer');
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function addTooltip(array $args): array
    {
        return $this->contentInjector->insertContentAboveElement($args, 'fix_text_tootltip', '#headers-menu');
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function addFormButtons(array $args): array
    {
        $parsedHtmlContent = $this->contentInjector->getParsedHtml('buttons');
        $buttonId = $this->contentInjector->findId($parsedHtmlContent)[0];

        if (isset($args['content']) && preg_match('/id=["\']' . $buttonId . '["\']/', \is_string($args['content']) ? $args['content'] : '')) {
            return $args;
        }

        return $this->contentInjector->insertContentAfterElement($args, 'buttons', '.formbuttons .btn.btn-primary.send');
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function addSelectFields(array $args): array
    {
        return $this->contentInjector->insertContentAfterElement($args, 'ai_select_fields', '#compose-options');
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function addHelpExamples(array $args): array
    {
        return $this->contentInjector->insertContentAboveElement($args, 'instruction_examples', '#layout-content');
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
        return $this->contentInjector->insertContentAboveElement($args, 'popup', '#headers-menu');
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
        $actionPrefix = 'plugin.AIComposePlugin_';
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
        }
    }
}
