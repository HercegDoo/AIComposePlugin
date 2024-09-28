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
        $this->plugin->register_action('plugin.aicr', [$this, 'aicr']);
        $this->plugin->register_action('plugin.aicdeletemessage', [$this, 'aicdeletemessage']);
        $this->plugin->register_action('plugin.getMessageById', [$this, 'getMessageById']);
        $this->plugin->include_script('assets/dist/settings.bundle.js');
    }

    public function aicresponses(): void
    {
        error_log('Uso u responses hendler');
        $this->plugin->include_script('assets/dist/settings.bundle.js');
    }

    public function aicresponsesrequest(): void
    {
        $rcmail = \rcmail::get_instance();
        $this->plugin->include_script('assets/dist/settings.bundle.js');

        header('Content-Type: application/json');

        try {
            $predefinedMessages = $rcmail->user->get_prefs()['predefinedMessages'] ?? [];
            $found = false;

            if (isset($_POST['id'])) {
                $id = $_POST['id'];
                foreach ($predefinedMessages as &$message) {
                    if ($message['id'] === $id) {
                        $message['title'] = $_POST['title'];
                        $message['value'] = $_POST['value'];
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                $predefinedMessage = [
                    'title' => $_POST['title'],
                    'value' => $_POST['value'],
                    'id' => uniqid('predefined-message-'),
                ];
                $predefinedMessages[] = $predefinedMessage;
            }

            // Sačuvaj promjene
            $rcmail->user->save_prefs(['predefinedMessages' => $predefinedMessages]);

            // Vraćamo uspješan odgovor
            echo json_encode([
                'status' => 'success',
                'returnValue' => $predefinedMessages,
            ]);
        } catch (\Throwable $e) {
            // Prikazujemo greške u logu
            error_log('Error message (Add or Edit) : ' . $e->getMessage());
            error_log('Error code (Add or Edit) : ' . $e->getCode());
            error_log('Error file (Add or Edit) : ' . $e->getFile());
            error_log('Error line (Add or Edit) : ' . $e->getLine());

            // Vraćamo grešku na frontend
            echo json_encode([
                'status' => 'error',
                'message' => 'Došlo je do greške prilikom obrade zahtjeva.',
            ]);
        }

        exit();
    }


    public function aicdeletemessage(): void
    {
        $rcmail = \rcmail::get_instance();
        $this->plugin->include_script('assets/dist/settings.bundle.js');

        header('Content-Type: application/json');

        try {
            $predefinedMessages = $rcmail->user->get_prefs()['predefinedMessages'] ?? [];
            $id = \rcube_utils::get_input_value('id', \rcube_utils::INPUT_POST);

            // Funkcija koja uklanja poruku po ID-ju
            $filteredMessageArray = $this->removeItemById($predefinedMessages, $id);

            // Sačuvaj izmjene
            $rcmail->user->save_prefs([
                'predefinedMessages' => $filteredMessageArray,
            ]);

            // Vraćanje uspješnog odgovora
            echo json_encode([
                'status' => 'success',
                'returnValue' => $filteredMessageArray,
            ]);
        } catch (\Throwable $e) {
            // Prikaz grešaka u logu
            error_log('Error message (Delete Message) : ' . $e->getMessage());
            error_log('Error code (Delete Message) : ' . $e->getCode());
            error_log('Error file (Delete Message) : ' . $e->getFile());
            error_log('Error line (Delete Message) : ' . $e->getLine());

            // Vraćanje greške na frontend
            echo json_encode([
                'status' => 'error',
                'message' => 'Došlo je do greške prilikom brisanja poruke.',
            ]);
        }

        exit();
    }


    public function aicresponsesgetrequest(): void
    {
        $rcmail = \rcmail::get_instance();
        header('Content-Type: application/json');

        try {
            // Dohvati unaprijed definirane poruke
            $predefinedMessages = $rcmail->user->get_prefs()['predefinedMessages'] ?? [];

            // Vraćanje uspješnog odgovora
            echo json_encode([
                'status' => 'success',
                'returnValue' => $predefinedMessages,
            ]);
        } catch (\Throwable $e) {
            // Prikaz grešaka u logu
            error_log('Error message: (Get All Messages) ' . $e->getMessage());
            error_log('Error code (Get All Messages) : ' . $e->getCode());
            error_log('Error file (Get All Messages) : ' . $e->getFile());
            error_log('Error line (Get All Messages) : ' . $e->getLine());

            // Vraćanje greške na frontend
            echo json_encode([
                'status' => 'error',
                'message' => 'Došlo je do greške prilikom dohvaćanja poruka.',
            ]);
        }

        exit();
    }


    public function getMessageById(): void
    {
        $rcmail = \rcmail::get_instance();
        header('Content-Type: application/json');

        try {
            // Preuzmi predefinedMessages iz prefs
            $predefinedMessages = $rcmail->user->get_prefs()['predefinedMessages'] ?? [];
            $id = \rcube_utils::get_input_value('id', \rcube_utils::INPUT_GET);

            // Ako je id postavljen, filtriraj poruke po id-u
            $return = $id ? array_values(array_filter($predefinedMessages, static fn($msg) => $msg['id'] === $id))[0] ?? [] : [];

            // Uspješan odgovor
            echo json_encode([
                'status' => 'success',
                'returnValue' => $return,
            ]);
        } catch (\Throwable $e) {
            // Prikaz grešaka u logu
            error_log('Error message (Get Message By Id) : ' . $e->getMessage());
            error_log('Error code (Get Message By Id) : ' . $e->getCode());
            error_log('Error file (Get Message By Id) : ' . $e->getFile());
            error_log('Error line (Get Message By Id) : ' . $e->getLine());

            // Vraćanje greške na frontend
            echo json_encode([
                'status' => 'error',
                'message' => 'Došlo je do greške prilikom dohvaćanja poruke.',
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
    public function addSettingsSection(array $args): array
    {
        $this->plugin->include_script('assets/dist/settings.bundle.js');
        // Definiramo novu sekciju (tab) unutar postavki
        $new_section = [
            'action' => 'plugin.aicresponses',
            'type' => 'link',
            'label' => 'airesponses',
            'title' => 'aimanageresponses',
            'id' => 'settingstabaipredefinedresponses',
        ];

        if (!isset($args['actions']) || !\is_array($args['actions'])) {
            $args['actions'] = [];
        }

        // Proveravamo da li sekcija već postoji kako ne bi došlo do dupliranja
        $already_exists = false;
        foreach ($args['actions'] as $action) {
            if ($action['label'] === 'airesponses') {
                $already_exists = true;
                break;
            }
        }

        // Ako sekcija ne postoji, dodajemo je
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

    private function removeItemById(array $items, $id)
    {
        $filteredItems = array_filter($items, static function ($item) use ($id) {
            return $item['id'] !== $id;
        });

        return array_values($filteredItems);
    }
}
