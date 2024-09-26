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
        $this->plugin->add_hook('settings_actions', [$this, 'addSettingsSection']);
        $this->plugin->add_hook('responses_list', [$this, 'responseHandler']);
        $this->plugin->register_action('plugin.aicresponses', [$this, 'aicresponses']);
        $this->plugin->register_action('plugin.aicresponsesrequest', [$this, 'aicresponsesrequest']);
        $this->plugin->register_action('plugin.aicresponsesgetrequest', [$this, 'aicresponsesgetrequest']);
    }

    public function aicresponses() : void
    {
        error_log('Uso u responses hendler');
        $this->plugin->include_script('assets/dist/settings.bundle.js');
    }

    public function aicresponsesrequest() : void
    {
        $rcmail = \rcmail::get_instance();
        $this->plugin->include_script('assets/dist/settings.bundle.js');
        $predefinedMessages = $rcmail->user->get_prefs()['predefinedMessages'] ?? [];
        $predefinedMessage = ['title' => $_POST['title'], 'value' => $_POST['value']];
        $predefinedMessages[] = $predefinedMessage;

        $rcmail->user->save_prefs([
            'predefinedMessages' => $predefinedMessages,
        ]);
    }

    public function aicresponsesgetrequest() : void
    {
        $rcmail = \rcmail::get_instance();
        $currentPrefs = $rcmail->user->get_prefs();

        // Vraćanje odgovora
        header('Content-Type: application/json');
        echo json_encode(['predefinedMessages' => $rcmail->user->get_prefs()['predefinedMessages'] ?? []]);
        exit;
    }


    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function preferencesSectionsList(array $args): array
    {
        error_log('AKcija u preferencesSectionsList: ' . print_r(\rcube_utils::get_input_value('action', \rcube_utils::INPUT_GET), true));
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
    public function addSettingsSection(array $args) : array
    {
        // Definiramo novu sekciju (tab) unutar postavki
        $new_section = [
            'action' => 'plugin.aicresponses',
            'type' => 'link',
            'label' => 'airesponses',
            'title' => 'aimanageresponses',
            'id' => 'settingstabaipredefinedresponses',
        ];

        if (!isset($args['actions']) || !is_array($args['actions'])) {
            $args['actions'] = [];
        }

        //Proveravamo da li sekcija već postoji kako ne bi došlo do dupliranja
        $already_exists = false;
        foreach ($args['actions'] as $action) {
            if ($action['label'] === 'airesponses') {
                $already_exists = true;
                break;
            }
        }

        // Ako sekcija ne postoji, dodajemo je
        if (!$already_exists ) {
            $args['actions'][] = $new_section; // Dodajemo u array
        }

        return $args;
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function preferencesList(array $args): array
    {
        //        $this->plugin->include_script('assets/dist/settings.bundle.js');
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
                    [
                        'title' => 'Input',
                        'content' => ' <input list="options" id="dynamicInput" placeholder="Unesite vrijednost ili odaberite" />

    <datalist id="options">
        <option value="Opcija 1">
        <option value="Opcija 2">
        <option value="Opcija 3">
    </datalist>',
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
        error_log('AKcija u preferencesSave ' . print_r(\rcube_utils::get_input_value('action', \rcube_utils::INPUT_GET), true));
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
                error_log('Moj args niz: ' . print_r($args, true));
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
