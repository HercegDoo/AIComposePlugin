<?php

namespace HercegDoo\AIComposePlugin\Actions\Settings;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;

class SaveInstruction extends AbstractAction
{

    protected function handler(): void
    {

        $rcmail = \rcmail::get_instance();

        // Uzimanje podataka iz POST zahtjeva
        $name    = trim(\rcube_utils::get_input_string('_name', \rcube_utils::INPUT_POST));
        $text    = trim(\rcube_utils::get_input_string('_text', \rcube_utils::INPUT_POST));

        $rcmail->output->add_handler('instructionslist', [$this, 'update_instructions']);

        // Pravljenje asocijativnog niza
        $response = [
            'name'    => $name,
            'data'    => $text,
        ];

        // Provjera da li su obavezna polja prazna
        if (empty($name) || empty($text)) {
            $rcmail->output->show_message('formincomplete', 'error');
            return;
        }

        // Spremanje podataka u user preferences pod nazivom 'predefinedInstructionsSet'
        $preferences = $rcmail->user->get_prefs();
        $preferences['predefinedInstructionsSet'] = $response;
        $rcmail->user->save_prefs($preferences);
        error_log("Predefinisane insttt" . print_r($preferences['predefinedInstructionsSet'], true));

        // Poruka o uspjehu
        $rcmail->output->show_message('successfullysaved', 'confirmation');
    }

    public static function update_instructions($attrib){

        $rcmail = \rcmail::get_instance();

        $attrib += ['id' => 'rcmresponseslist', 'tagname' => 'table'];

        $preferences = $rcmail->user->get_prefs();
        $saved_responses = $preferences['predefinedInstructionsSet'] ?? [];

        $plugin = [
            'list' => [
                [
                    'id' => 'static-1234567890abcdef',
                    'name' => 'Default Response 4',
                ],
                [
                    'id' => 'static-abcdef1234567890',
                    'name' => 'Default Response 7',
                ],
                [
                    'id' => 'user-9876543210fedcba',
                    'name' => 'User Custom Response',
                ],
            ],
            'cols' => ['name'],
        ];

        $out = \rcmail_action::table_output($attrib, $plugin['list'], $plugin['cols'], 'id');

        $readonly_responses = [];
        foreach ($plugin['list'] as $item) {
            if (!empty($item['static'])) {
                $readonly_responses[] = $item['id'];
            }
        }

        // set client env
        $rcmail->output->add_gui_object('instructionslist', $attrib['id']);
        $rcmail->output->set_env('readonly_responses', $readonly_responses);

        return $out;
    }


    protected function validate(): void
    {
        // TODO: Implement validate() method.
    }
}