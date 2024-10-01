<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tasks;

use HercegDoo\AIComposePlugin\Actions\GetInstructionsAction;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use html;

class SettingsTask extends AbstractTask
{
    public function init(): void
    {
        $this->plugin->add_hook('preferences_sections_list', [$this, 'preferencesSectionsList']);
        $this->plugin->add_hook('preferences_list', [$this, 'preferencesList']);
        $this->plugin->add_hook('preferences_save', [$this, 'preferencesSave']);
        $this->plugin->add_hook('settings_actions', [$this, 'addSettingsSection']);
        $this->plugin->add_hook('settings_actions', [$this, 'addSCustomSection']);
        $this->plugin->register_action('plugin.aicresponses', [$this, 'aicresponses']);
        $this->plugin->register_action('plugin.custom', [$this, 'custom']);
        $this->plugin->register_action('plugin.customcreate', [$this, 'customcreate']);

        GetInstructionsAction::register();
    }

    public function custom() : void
    {
        $rcmail = \rcmail::get_instance();

        $rcmail->output->set_pagetitle($rcmail->gettext('preferences'));  // Promijenjeno u preferences
        $rcmail->output->include_script('list.js');
        $rcmail->output->add_label('updatepreferencesconfirm');  // Promijenjen label
        $rcmail->output->add_handlers(['instructionslist' => [$this, 'preferences_list']]);
        $rcmail->output->send('AIComposePlugin.custom');
    }

    public static function preferences_list($attrib)
    {
        $rcmail = \rcmail::get_instance();

        $attrib += ['id' => 'rcmpreferenceslist', 'tagname' => 'table'];  // ID promijenjen u preferenceslist

        // Dohvatanje korisničkih postavki
        $user_prefs = $rcmail->user->get_prefs();  // Dohvati sve korisničke postavke

        // Primjer korisničke postavke koje želimo prikazati
        $plugin = $rcmail->plugins->exec_hook('preferences_list', [
            'list' => $user_prefs,  // Umjesto responses, koristimo user preferences
            'cols' => ['preference', 'value']  // Kolone: preference name i value
        ]);

        // Generišemo HTML izlaz koristeći table_output
        $out = \rcmail_action::table_output($attrib, $plugin['list'], $plugin['cols'], 'id');

        // Nema readonly items ovdje, ali ako postoji, možeš ga prilagoditi
        $readonly_prefs = [];
        foreach ($plugin['list'] as $item) {
            if (!empty($item['readonly'])) {
                $readonly_prefs[] = $item['id'];
            }
        }

        // set client env
        $rcmail->output->add_gui_object('preferenceslist', $attrib['id']);
        $rcmail->output->set_env('readonly_prefs', $readonly_prefs);

        return $out;
    }

    public function customcreate(){
        $this->plugin->register_handler('plugin.custom', [$this, 'customHandler']);
        $this->plugin->include_script('assets/dist/settings.bundle.js');
    }



public function customHandler() {

    $this->plugin->include_script('assets/dist/settings.bundle.js');
    $rcmail = \rcmail::get_instance();
    $user = $rcmail->user;

    // Kreiranje divova za input polje
    $input_field = new \html_inputfield([
        'name' => 'custom_input',
        'class' => 'form-control',
        'placeholder' => $this->plugin->gettext('input_placeholder'),
    ]);

    // Labela za input polje
    $input_label = \html::label('custom_input', \rcube::Q($this->plugin->gettext('custominput')));

    // Div za input polje i labelu
    $input_div = \html::div(
        ['class' => 'input-field-container'], // klasa za stilizaciju
        $input_label . $input_field->show()
    );

    // Kreiranje textarea
    $textarea = new \html_textarea();
    $textarea_html = $textarea->show('', [
        'name' => 'custom_textarea',
        'rows' => 5,
        'cols' => 40,
        'class' => 'form-control',
    ]);

    // Labela za textarea
    $textarea_label = \html::label('custom_textarea', \rcube::Q($this->plugin->gettext('customtextarea')));

    // Div za textarea i labelu
    $textarea_div = \html::div(
        ['class' => 'textarea-container'], // klasa za stilizaciju
        $textarea_label . $textarea_html
    );

    // Kreiranje glavnog izlaza za input i textarea
    $out = \html::div(
        ['class' => 'form-fields'], // glavni div koji sadrži input i textarea
        $input_div . $textarea_div
    );

    // Generisanje scroller sadržaja
    $scroller_content = \html::tag(
        'tbody',
        \html::tag(
            'tr',
            [
                'id' => 'rcmrow',
                'role' => 'option',
                'aria-labelledby' => 'l:rcmrow',
                'class' => 'selected focused',
                'aria-selected' => 'true',
                'style' => 'display: none;'
            ],
            \html::tag('td', ['id' => 'empty'], 'The list is empty. Use the Create button to add a new record.')
        )
    );

    // Kreiranje scroller div-a
    $scroller_div = \html::div(
        ['class' => 'scroller'],
        \html::tag('table', [
            'id' => 'responses-table',
            'class' => 'listing',
            'role' => 'listbox',
            'data-list' => 'responses_list',
            'data-label-ext' => 'Use the Create button to add a new record.',
            'data-create-command' => 'add',
            'tabindex' => '0',
        ], $scroller_content) .
        \html::div(['class' => 'listing-info'], 'The list is empty. Use the Create button to add a new record.')
    );

    // Vraćanje izlaza sa box formcontent i scroller kao sibling
    return \html::div(
            ['class' => 'box formcontent'],
            $out // Sadržaj forme u divovima
        );
    }


    public function infohtml()
    {
        $rcmail = \rcmail::get_instance();
        $user = $rcmail->user;

        // Kreiranje divova za input polje
        $input_field = new \html_inputfield([
            'name' => 'custom_input',
            'class' => 'form-control',
            'placeholder' => $this->plugin->gettext('input_placeholder'),
        ]);

        // Labela za input polje
        $input_label = \html::label('custom_input', \rcube::Q($this->plugin->gettext('custominput')));

        // Div za input polje i labelu
        $input_div = \html::div(
            ['class' => 'input-field-container'], // klasa za stilizaciju
            $input_label . $input_field->show()
        );

        // Kreiranje textarea
        $textarea = new \html_textarea();
        $textarea_html = $textarea->show('', [
            'name' => 'custom_textarea',
            'rows' => 5,
            'cols' => 40,
            'class' => 'form-control',
        ]);

        // Labela za textarea
        $textarea_label = \html::label('custom_textarea', \rcube::Q($this->plugin->gettext('customtextarea')));

        // Div za textarea i labelu
        $textarea_div = \html::div(
            ['class' => 'textarea-container'], // klasa za stilizaciju
            $textarea_label . $textarea_html
        );

        // Kreiranje glavnog izlaza za input i textarea
        $out = \html::div(
            ['class' => 'form-fields'], // glavni div koji sadrži input i textarea
            $input_div . $textarea_div
        );

        // Generisanje scroller sadržaja
        $scroller_content = \html::tag(
            'tbody',
            \html::tag(
                'tr',
                [
                    'id' => 'rcmrow',
                    'role' => 'option',
                    'aria-labelledby' => 'l:rcmrow',
                    'class' => 'selected focused',
                    'aria-selected' => 'true',
                    'style' => 'display: none;'
                ],
                \html::tag('td', ['id' => 'empty'], 'The list is empty. Use the Create button to add a new record.')
            )
        );

        // Kreiranje scroller div-a
        $scroller_div = \html::div(
            ['class' => 'scroller'],
            \html::tag('table', [
                'id' => 'responses-table',
                'class' => 'listing',
                'role' => 'listbox',
                'data-list' => 'responses_list',
                'data-label-ext' => 'Use the Create button to add a new record.',
                'data-create-command' => 'add',
                'tabindex' => '0',
            ], $scroller_content) .
            \html::div(['class' => 'listing-info'], 'The list is empty. Use the Create button to add a new record.')
        );

        // Vraćanje izlaza sa box formcontent i scroller kao sibling
        return \html::div(
                ['class' => 'box formcontent'],
                $out // Sadržaj forme u divovima
            ) . $scroller_div; // Dodaj scroller kao sibling
    }


//    public function addButtonsToHeader()
//    {
//        // Kreiramo dva dugmeta
//        $button1 = \html::tag('button', ['class' => 'btn btn-primary', 'type' => 'button'], 'Button 1');
//        $button2 = \html::tag('button', ['class' => 'btn btn-secondary', 'type' => 'button'], 'Button 2');
//
//        // Grupisanje dugmadi unutar div-a
//        $buttons_div = \html::div(['class' => 'buttons-container'], $button1 . $button2);
//
//        // Pronalazimo 'header' div unutar 'layout-content'
//        $layout_content = \rcmail::get_instance()->output->get_env('layout-content');
//
//        // Dodaj dugmad u 'header' div unutar layout-content-a
//        return \html::div(
//            ['id' => 'layout-content'],
//            \html::div(
//                ['class' => 'header'],
//                $buttons_div // Dugmad unutar 'header' diva
//            )
//        );
//    }


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
        $new_section = [
            'action' => 'plugin.aicresponses',
            'type' => 'link',
            'label' => 'AIComposePlugin.ai_predefined_section_title',
            'title' => 'aimanageresponses',
            'id' => 'settingstabaipredefinedresponses',
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

    public function addSCustomSection(array $args): array
    {
        $new_section = [
            'action' => 'plugin.custom',
            'type' => 'link',
            'label' => 'title',
            'title' => 'customsection',
            'id' => 'mycustomsection',
        ];

        if (!isset($args['actions']) || !\is_array($args['actions'])) {
            $args['actions'] = [];
        }

        $already_exists = false;
        foreach ($args['actions'] as $action) {
            if ($action['label'] === 'title') {
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
