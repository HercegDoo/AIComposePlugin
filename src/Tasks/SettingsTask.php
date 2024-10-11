<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tasks;

use HercegDoo\AIComposePlugin\AIEmailService\Settings;

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
    }

    /**
     * @param array<string, string> $args
     */
    public function base_predefined_instructions(array $args = []): void
    {
        $rcmail = \rcmail::get_instance();

        $this->plugin->include_script('assets/dist/settings.bundle.js');

        $rcmail->output->set_pagetitle($rcmail->gettext('AIComposePlugin.ai_predefined_section_title'));
        $rcmail->output->add_handlers(['instructionslist' => [$this, 'responses_listt']]);
        $rcmail->output->send('AIComposePlugin.basepredefinedinstructions');
    }

    /**
     * Create template object 'responseslist'.
     *
     * @param array<string, string> $attrib
     *
     * @return string HTML table output
     */
    public static function responses_listt(array $attrib): string
    {
        $rcmail = \rcmail::get_instance();
        $attrib += ['id' => 'rcmresponseslist', 'tagname' => 'table'];

        $predefinedInstructions = $rcmail->user->get_prefs()['predefinedInstructions'] ?? [];
        $instructionsArray = [];
        //                $predefinedInstructions= [];
        //                $rcmail->user->save_prefs(['predefinedInstructions' => $predefinedInstructions]);

        foreach ($predefinedInstructions as $instruction) {
            $instructionsArray[] = ['id' => $instruction['id'], 'name' => $instruction['title']];
        }

        $plugin = [
            'list' => $instructionsArray,
            'cols' => ['name'],
        ];

        $out = \rcmail_action::table_output($attrib, $plugin['list'], $plugin['cols'], 'id');

        // set client env
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
     * @return array<string, array<string, array<string, mixed>|string>>
     */
    public function preferencesSectionsList(array $args): array
    {
        error_log("Args u pref sections " . print_r($args, true));
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
        $this->plugin->include_stylesheet('assets/src/settings/style.css');
        /** @var array<string, array<string, mixed>> $blocks */
        $blocks = $args['blocks'] ?? [];

        if (isset($args['section']) && $args['section'] == 'aic') {
            $blocks['general'] = [
                'name' => 'General Settings',
                'options' => [
                    [
                        'title' => 'Style',
                        'content' => $this->getDropdownHtml(Settings::getStyles(), 'style', Settings::getDefaultStyle()),
                    ],
                    [
                        'title' => 'Creativity',
                        'content' => $this->getDropdownHtml(Settings::getCreativities(), 'creativity', Settings::getCreativity()),
                    ],
                    [
                        'title' => 'Length',
                        'content' => $this->getDropdownHtml(Settings::getLengths(), 'length', Settings::getDefaultLength()),
                    ],
                    [
                        'title' => 'Language',
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
            $dropdown .= '<option ' . ($option === $default ? 'selected' : '') . ' value="' . ($option) . '">' . ($this->translation('ai_' . $name . '_' . strtolower($option))) . '</option>';
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

    private function translation(string $key): string
    {
        return \rcmail::get_instance()->gettext("AIComposePlugin.{$key}");
    }
}
