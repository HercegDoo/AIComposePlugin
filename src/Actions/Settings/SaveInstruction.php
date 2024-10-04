<?php

namespace HercegDoo\AIComposePlugin\Actions\Settings;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;

class SaveInstruction extends AbstractAction
{

    protected function handler(): void
    {
        error_log("Uso u error handler");

        $rcmail = \rcmail::get_instance();

        // Uzimanje podataka iz POST zahtjeva
        $name    = trim(\rcube_utils::get_input_string('_name', \rcube_utils::INPUT_POST));
        $text    = trim(\rcube_utils::get_input_string('_text', \rcube_utils::INPUT_POST));

        $rcmail->output->add_handlers(['instructionslist' => [$this, 'update_instructions']]);
        $predefinedInstructions = $rcmail->user->get_prefs()['predefinedInstructions'] ?? [];

        // Pravljenje asocijativnog niza
        $response = [
            'title'    => $name,
            'message'    => $text,
            'id'=> uniqid(),
        ];


        error_log("Napravljeni asocijativni niz: " . print_r($response, true));

        // Provjera da li su obavezna polja prazna
        if (empty($name) || empty($text)) {
            $rcmail->output->show_message('formincomplete', 'error');
            return;
        }

        // Spremanje podataka u user preferences pod nazivom 'predefinedInstructionsSet'
        $predefinedInstructions[] = $response;
        $rcmail->user->save_prefs(['predefinedInstructions' => $predefinedInstructions]);


        // Poruka o uspjehu
        $rcmail->output->show_message('successfullysaved', 'confirmation');
        error_log("Nakon poruke o uspjehu");
        $rcmail->output->send('AIComposePlugin.custom');
    }

    public static function update_instructions($attrib){

        error_log("Uso u update instructions");
        $rcmail = \rcmail::get_instance();

        $attrib += ['id' => 'rcmresponseslist', 'tagname' => 'table'];

        $preferences = $rcmail->user->get_prefs();
        $saved_responses = $preferences['predefinedInstructionsSet'] ?? [];

        error_log("Sacuvani odgovori u update handler" . print_r($saved_responses, true));

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

        error_log("kraj update instructions");

        return $out;
    }


    protected function validate(): void
    {
        // TODO: Implement validate() method.
    }
}