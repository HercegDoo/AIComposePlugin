<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tasks;

use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use HercegDoo\AIComposePlugin\Tasks\AbstractTask;
use HercegDoo\AIComposePlugin\Utilities\XSSProtection;

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

        $rcmail->output->set_pagetitle($rcmail->gettext('AIComposePlugin.ai_predefined_section_title'));
        $rcmail->output->add_handlers(['instructionslist' => [$this, 'instructions_list']]);
        $rcmail->output->send('AIComposePlugin.base_predefined_instructions');
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
            'label' => 'AIComposePlugin.ai_predefined_section_title',
            'title' => 'predefinedinstructions',
            'id' => 'aicpredefinedinstructions',
        ];

        if (!isset($args['actions']) || !\is_array($args['actions'])) {
            $args['actions'] = [];
        }

        $already_exists = false;
        foreach ($args['actions'] as $action) {
            if ($action['label'] === 'AIComposePlugin.ai_predefined_section_title') {
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
                        'title' => $this->translation('ai_label_style'),
                        'content' => $this->getDropdownHtml(Settings::getStyles(), 'style', Settings::getDefaultStyle()),
                    ],
                    [
                        'title' => $this->translation('ai_label_creativity'),
                        'content' => $this->getDropdownHtml(Settings::getCreativities(), 'creativity', Settings::getCreativity()),
                    ],
                    [
                        'title' => $this->translation('ai_label_length'),
                        'content' => $this->getDropdownHtml(Settings::getLengths(), 'length', Settings::getDefaultLength()),
                    ],
                    [
                        'title' => $this->translation('ai_label_language'),
                        'content' => $this->getDropdownHtml(Settings::getLanguages(), 'language', Settings::getDefaultLanguage()),
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
            if (\is_array($data) && isset($data['aic'])) {
                $aicData = $data['aic'];
            }
            $rcmail = \rcmail::get_instance();

            if ($this->validateSettingsValues($aicData['style'], Settings::getStyles()) && $this->validateSettingsValues($aicData['creativity'], Settings::getCreativities())
                && $this->validateSettingsValues($aicData['length'], Settings::getLengths()) && $this->validateSettingsValues($aicData['language'], Settings::getLanguages())
            ) {
                $rcmail->user->save_prefs([
                    'aicDefaults' => $aicData,
                ]);
            }
        }

        return $args;
    }

    /**
     * @param string[] $options
     */
    private function getDropdownHtml(array $options, string $name, ?string $default = null): string
    {
        $dropdown = '<select name="data[aic][' . $name . ']">'; // Ispravno ime za formu
        foreach ($options as $option) {
            $selected = ($option === $default ? 'selected' : '');
            $safeValue = XSSProtection::escapeAttribute((string) $option);
            $safeLabel = XSSProtection::escape($this->translation('ai_' . $name . '_' . strtolower($option)));
            $dropdown .= '<option ' . $selected . ' value="' . $safeValue . '">' . $safeLabel . '</option>';
        }
        $dropdown .= '</select>';

        return $dropdown;
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
            // Sanitizar valor e label para prevenir XSS
            $safeValue = XSSProtection::escapeAttribute($value);
            $safeLabel = XSSProtection::escape($label);
            $dropdown .= \sprintf('<option value="%s" %s>%s</option>', $safeValue, $selected, $safeLabel);
        }

        $dropdown .= '</select>';

        return $dropdown;
    }

    /**
     * @param string[] $values
     */
    private function validateSettingsValues(string $selectedValue, array $values): bool
    {
        return \in_array($selectedValue, $values, true);
    }
}
