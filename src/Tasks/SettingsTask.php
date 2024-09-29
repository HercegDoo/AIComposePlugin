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
        $this->plugin->register_action('plugin.aicresponses', [$this, 'aicresponses']);
        $this->plugin->register_action('plugin.aicCreateOrEdit', [$this, 'aicCreateOrEdit']);
        $this->plugin->register_action('plugin.aicGetAllInstructions', [$this, 'aicGetAllInstructions']);
        $this->plugin->register_action('plugin.aicDeleteInstruction', [$this, 'aicDeleteInstruction']);
        $this->plugin->register_action('plugin.getInstructionById', [$this, 'getInstructionById']);
    }

    public function aicresponses(): void
    {
        try {
            $this->plugin->include_script('assets/dist/settings.bundle.js');
        } catch (\Throwable $e) {
            error_log('Error message (Add or Edit) : ' . $e->getMessage());
            error_log('Error code (Add or Edit) : ' . $e->getCode());
            error_log('Error file (Add or Edit) : ' . $e->getFile());
            error_log('Error line (Add or Edit) : ' . $e->getLine());
            \rcmail::get_instance()->output->show_message($this->translation('ai_predefined_content_error'), 'error');
        }
    }

    public function aicCreateOrEdit(): void
    {
        $rcmail = \rcmail::get_instance();
        $this->plugin->include_script('assets/dist/settings.bundle.js');

        header('Content-Type: application/json');

        try {
            $predefinedInstructions = $rcmail->user->get_prefs()['predefinedInstructions'] ?? [];
            $found = false;

            if (isset($_POST['id'])) {
                $id = $_POST['id'];
                foreach ($predefinedInstructions as &$instruction) {
                    if ($instruction['id'] === $id) {
                        $instruction['title'] = $_POST['title'];
                        $instruction['value'] = $_POST['value'];
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                $predefinedInstruction = [
                    'title' => $_POST['title'],
                    'value' => $_POST['value'],
                    'id' => uniqid('predefined-instruction-'),
                ];
                $predefinedInstructions[] = $predefinedInstruction;
            }

            $rcmail->user->save_prefs(['predefinedInstructions' => $predefinedInstructions]);

            echo json_encode([
                'status' => 'success',
                'returnValue' => $predefinedInstructions,
            ]);
        } catch (\Throwable $e) {
            error_log('Error message (Add or Edit) : ' . $e->getMessage());
            error_log('Error code (Add or Edit) : ' . $e->getCode());
            error_log('Error file (Add or Edit) : ' . $e->getFile());
            error_log('Error line (Add or Edit) : ' . $e->getLine());

            echo json_encode([
                'status' => 'error',
                'message' => $this->translation('ai_predefined_request_error'),
            ]);
        }

        exit();
    }

    public function aicDeleteInstruction(): void
    {
        $rcmail = \rcmail::get_instance();
        $this->plugin->include_script('assets/dist/settings.bundle.js');

        header('Content-Type: application/json');

        try {
            $predefinedInstructions = $rcmail->user->get_prefs()['predefinedInstructions'] ?? [];
            $id = \rcube_utils::get_input_value('id', \rcube_utils::INPUT_POST);
            $filteredInstructionsArray = $this->removeItemById($predefinedInstructions, $id);

            $rcmail->user->save_prefs([
                'predefinedInstructions' => $filteredInstructionsArray,
            ]);

            echo json_encode([
                'status' => 'success',
                'returnValue' => $filteredInstructionsArray,
            ]);
        } catch (\Throwable $e) {
            error_log('Error message (Delete Message) : ' . $e->getMessage());
            error_log('Error code (Delete Message) : ' . $e->getCode());
            error_log('Error file (Delete Message) : ' . $e->getFile());
            error_log('Error line (Delete Message) : ' . $e->getLine());

            echo json_encode([
                'status' => 'error',
                'message' => $this->translation('ai_predefined_delete_error'),
            ]);
        }

        exit();
    }

    public function aicGetAllInstructions(): void
    {
        $rcmail = \rcmail::get_instance();
        header('Content-Type: application/json');

        try {
            $predefinedInstructions = $rcmail->user->get_prefs()['predefinedInstructions'] ?? [];

            echo json_encode([
                'status' => 'success',
                'returnValue' => $predefinedInstructions,
            ]);
        } catch (\Throwable $e) {
            error_log('Error message: (Get All Messages) ' . $e->getMessage());
            error_log('Error code (Get All Messages) : ' . $e->getCode());
            error_log('Error file (Get All Messages) : ' . $e->getFile());
            error_log('Error line (Get All Messages) : ' . $e->getLine());

            echo json_encode([
                'status' => 'error',
                'message' => $this->translation('ai_predefined_get_all_instructions_error'),
            ]);
        }

        exit();
    }

    public function getInstructionById(): void
    {
        $rcmail = \rcmail::get_instance();
        header('Content-Type: application/json');

        try {
            $predefinedInstructions = $rcmail->user->get_prefs()['predefinedInstructions'] ?? [];
            $id = \rcube_utils::get_input_value('id', \rcube_utils::INPUT_GET);

            $return = $id ? array_values(array_filter($predefinedInstructions, static fn ($msg) => $msg['id'] === $id))[0] ?? [] : [];

            echo json_encode([
                'status' => 'success',
                'returnValue' => $return,
            ]);
        } catch (\Throwable $e) {
            error_log('Error message (Get Message By Id) : ' . $e->getMessage());
            error_log('Error code (Get Message By Id) : ' . $e->getCode());
            error_log('Error file (Get Message By Id) : ' . $e->getFile());
            error_log('Error line (Get Message By Id) : ' . $e->getLine());

            echo json_encode([
                'status' => 'error',
                'message' => $this->translation('ai_predefined_get_specific_instruction_error'),
            ]);
        }

        exit;
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
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
    public function addSettingsSection(array $args): array
    {
        // Originalni string
        $airesponses = "airesponses";

// Uklanjanje svih '[' i ']' znakova iz stringa
        $result = str_replace(['[', ']'], '', $airesponses);



        $new_section = [
            'action' => 'plugin.aicresponses',
            'type' => 'link',
            'label' => $this->translation('ai_predefined_section_title'),
            'title' => 'aimanageresponses',
            'id' => 'settingstabaipredefinedresponses',
        ];

        if (!isset($args['actions']) || !\is_array($args['actions'])) {
            $args['actions'] = [];
        }

        // Proveravamo da li sekcija već postoji kako ne bi došlo do dupliranja
        $already_exists = false;
        foreach ($args['actions'] as $action) {
            if ($action['label'] === $this->translation('ai_predefined_section_title')) {
                $already_exists = true;
                break;
            }
        }

        if (!$already_exists) {
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

    /**
     * @param array<array<string, string>> $items
     * @param null|array<string>|string    $id
     *
     * @return array<array<string, string>>
     */
    private function removeItemById(array $items, $id): array
    {
        $filteredItems = array_filter($items, static function ($item) use ($id) {
            return $item['id'] !== $id;
        });

        return array_values($filteredItems);
    }
}
