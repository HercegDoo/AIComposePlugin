<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tasks;

use HercegDoo\AIComposePlugin\AIEmailService\Summary\SummaryDisplayPreferences;
use HercegDoo\AIComposePlugin\AIEmailService\Summary\SummaryTargetLanguage;
use HercegDoo\AIComposePlugin\AIEmailService\Translation\TranslationDisplayPreferences;

class SettingsTask extends AbstractTask
{
    public function init(): void
    {
        $this->plugin->add_hook('preferences_sections_list', [$this, 'preferencesSectionsList']);
        $this->plugin->add_hook('preferences_list', [$this, 'preferencesList']);
        $this->plugin->add_hook('preferences_save', [$this, 'preferencesSave']);
        $this->plugin->add_hook('settings_actions', [$this, 'addPredefinedInstructionsSection']);
        $this->plugin->register_action('plugin.basepredefinedinstructions', [$this, 'base_predefined_instructions']);
        $this->plugin->include_stylesheet('assets/src/settings/style.css');
        $this->plugin->add_texts('src/localization/labels/', ['ai_predefined_section_title']);
    }

    /**
     * @param array<string, string> $args
     */
    public function base_predefined_instructions(array $args = []): void
    {
        $rcmail = \rcmail::get_instance();
        $this->loadTranslations();
        $rcmail->output->set_env('aiPredefinedInstructions', $rcmail->user->get_prefs()['predefinedInstructions'] ?? []);
        $this->plugin->include_script('assets/dist/settings.bundle.js');

        $rcmail->output->set_pagetitle($rcmail->gettext('aicomposeplugin.ai_predefined_section_title'));
        $rcmail->output->add_handlers(['instructionslist' => [$this, 'instructions_list']]);
        $rcmail->output->send('aicomposeplugin.base_predefined_instructions');
    }

    /**
     * Create template object 'responseslist'.
     *
     * @param array<string, string> $attrib
     *
     * @return string HTML table output
     */
    public static function instructions_list(array $attrib): string
    {
        $rcmail = \rcmail::get_instance();
        $attrib += ['id' => 'rcminstructionslist', 'tagname' => 'table'];

        $predefinedInstructions = $rcmail->user->get_prefs()['predefinedInstructions'] ?? [];
        $instructionsArray = [];

        foreach ($predefinedInstructions as $instruction) {
            $instructionsArray[] = ['id' => $instruction['id'], 'name' => $instruction['title']];
        }

        $plugin = [
            'list' => $instructionsArray,
            'cols' => ['name'],
        ];

        $out = \rcmail_action::table_output($attrib, $plugin['list'], $plugin['cols'], 'id');

        $rcmail->output->add_gui_object('instructionslist', $attrib['id']);

        return $out;
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function addPredefinedInstructionsSection(array $args): array
    {
        $new_section = [
            'action' => 'plugin.basepredefinedinstructions',
            'type' => 'link',
            'label' => 'aicomposeplugin.ai_predefined_section_title',
            'title' => 'predefinedinstructions',
            'id' => 'aicpredefinedinstructions',
        ];

        if (!isset($args['actions']) || !\is_array($args['actions'])) {
            $args['actions'] = [];
        }

        $already_exists = false;
        foreach ($args['actions'] as $action) {
            if ($action['label'] === 'aicomposeplugin.ai_predefined_section_title') {
                $already_exists = true;
                break;
            }
        }

        if (!$already_exists) {
            $args['actions'][] = $new_section;
        }

        return $args;
    }

    /**
     * @param array<string, array<string, array<string, mixed>|string>> $args
     *
     * @return array<string, array<string, array<string, mixed>|string>>
     */
    public function preferencesSectionsList(array $args): array
    {
        /** @var array<string, array<string, mixed>> $list */
        $list = $args['list'] ?? [];

        $list['aic'] = [
            'id' => 'aic',
            'section' => $this->translation('ai_compose_settings'),
        ];

        $args['list'] = $list;

        return $args;
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function preferencesList(array $args): array
    {
        /** @var array<string, array<string, mixed>> $blocks */
        $blocks = $args['blocks'] ?? [];

        if (isset($args['section']) && $args['section'] == 'aic') {
            $blocks['general'] = [
                'name' => $this->translation('ai_general_settings'),
                'options' => [
                    [
                        'title' => $this->translation('ai_compose_hide_show'),
                        'content' => $this->getDropdownShow(),
                    ],
                    [
                        'title' => $this->translation('ai_summary_language_setting'),
                        'content' => $this->getSummaryLanguageDropdown(),
                    ],
                    [
                        'title' => $this->translation('ai_summary_hover_setting'),
                        'content' => $this->getVisibilityDropdown(SummaryDisplayPreferences::HOVER),
                    ],
                    [
                        'title' => $this->translation('ai_summary_message_setting'),
                        'content' => $this->getVisibilityDropdown(SummaryDisplayPreferences::MESSAGE),
                    ],
                    [
                        'title' => $this->translation('ai_translation_message_setting'),
                        'content' => $this->getVisibilityDropdown(TranslationDisplayPreferences::MESSAGE),
                    ],
                ],
            ];

            $args['blocks'] = $blocks;
        }

        return $args;
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function preferencesSave(array $args): array
    {
        if ($args['section'] === 'aic') {
            $data = \rcube_utils::get_input_value('data', \rcube_utils::INPUT_POST);
            $aicData = [];
            if (\is_array($data) && isset($data['aic']) && \is_array($data['aic'])) {
                $aicData = $data['aic'];
            }
            $rcmail = \rcmail::get_instance();
            $visibility = $aicData['pluginVisibility'] ?? null;
            $summaryLanguage = $aicData['summaryLanguage'] ?? null;
            $defaults = $rcmail->user->get_prefs()['aicDefaults'] ?? [];
            if (!\is_array($defaults)) {
                $defaults = [];
            }
            $summaryHover = $aicData[SummaryDisplayPreferences::HOVER]
                ?? SummaryDisplayPreferences::choice($defaults, SummaryDisplayPreferences::HOVER);
            $summaryMessage = $aicData[SummaryDisplayPreferences::MESSAGE]
                ?? SummaryDisplayPreferences::choice($defaults, SummaryDisplayPreferences::MESSAGE);
            $translationMessage = $aicData[TranslationDisplayPreferences::MESSAGE]
                ?? SummaryDisplayPreferences::choice($defaults, TranslationDisplayPreferences::MESSAGE);
            if (\in_array($visibility, ['show', 'hide'], true)
                && \is_string($summaryLanguage)
                && SummaryTargetLanguage::isValid($summaryLanguage, $rcmail->list_languages())
                && \is_string($summaryHover)
                && SummaryDisplayPreferences::isValid($summaryHover)
                && \is_string($summaryMessage)
                && SummaryDisplayPreferences::isValid($summaryMessage)
                && \is_string($translationMessage)
                && SummaryDisplayPreferences::isValid($translationMessage)) {
                $defaults['pluginVisibility'] = $visibility;
                $defaults['summaryLanguage'] = $summaryLanguage;
                $defaults[SummaryDisplayPreferences::HOVER] = $summaryHover;
                $defaults[SummaryDisplayPreferences::MESSAGE] = $summaryMessage;
                $defaults[TranslationDisplayPreferences::MESSAGE] = $translationMessage;
                $prefs = $args['prefs'] ?? [];
                if (!\is_array($prefs)) {
                    $prefs = [];
                }
                $prefs['aicDefaults'] = $defaults;
                $args['prefs'] = $prefs;
            } else {
                $args['abort'] = true;
                $args['result'] = false;
            }
        }

        return $args;
    }

    private function getDropdownShow(): string
    {
        $options = [
            'show' => $this->translation('ai_compose_show'),
            'hide' => $this->translation('ai_compose_hide'),
        ];

        $defaultValue = \rcmail::get_instance()->user->get_prefs()['aicDefaults']['pluginVisibility'] ?? 'show';

        $dropdown = '<select name="data[aic][pluginVisibility]">';

        foreach ($options as $value => $label) {
            $selected = ($defaultValue === $value) ? 'selected' : '';
            $dropdown .= \sprintf('<option value="%s" %s>%s</option>', $value, $selected, $label);
        }

        $dropdown .= '</select>';

        return $dropdown;
    }

    private function getSummaryLanguageDropdown(): string
    {
        $rcmail = \rcmail::get_instance();
        $languages = $rcmail->list_languages();
        $options = [
            SummaryTargetLanguage::ROUNDCUBE => $this->translation('ai_summary_language_roundcube'),
            SummaryTargetLanguage::ORIGINAL => $this->translation('ai_summary_language_original'),
        ] + $languages;
        $defaults = $rcmail->user->get_prefs()['aicDefaults'] ?? [];
        $selected = \is_array($defaults) ? ($defaults['summaryLanguage'] ?? SummaryTargetLanguage::ROUNDCUBE) : SummaryTargetLanguage::ROUNDCUBE;
        if (!\is_string($selected) || !SummaryTargetLanguage::isValid($selected, $languages)) {
            $selected = SummaryTargetLanguage::ROUNDCUBE;
        }

        $dropdown = '<select name="data[aic][summaryLanguage]">';
        foreach ($options as $value => $label) {
            $dropdown .= '<option value="' . htmlspecialchars($value, \ENT_QUOTES, 'UTF-8') . '"' .
                ($value === $selected ? ' selected' : '') . '>' . htmlspecialchars($label, \ENT_QUOTES, 'UTF-8') . '</option>';
        }

        return $dropdown . '</select>';
    }

    private function getVisibilityDropdown(string $preference): string
    {
        $defaults = \rcmail::get_instance()->user->get_prefs()['aicDefaults'] ?? [];
        $selected = SummaryDisplayPreferences::choice(\is_array($defaults) ? $defaults : [], $preference);
        $options = [
            SummaryDisplayPreferences::SHOW => $this->translation('ai_compose_show'),
            SummaryDisplayPreferences::HIDE => $this->translation('ai_compose_hide'),
        ];

        $dropdown = '<select name="data[aic][' . $preference . ']">';
        foreach ($options as $value => $label) {
            $dropdown .= '<option value="' . $value . '"' . ($selected === $value ? ' selected' : '') . '>'
                . htmlspecialchars($label, \ENT_QUOTES, 'UTF-8') . '</option>';
        }

        return $dropdown . '</select>';
    }
}
