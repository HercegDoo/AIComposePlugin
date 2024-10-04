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
        $rcmail->output->add_handler('instructionslist', [$this, 'update_instructions']);

        // Poruka o uspjehu
        $rcmail->output->show_message('successfullysaved', 'confirmation');
        $rcmail->output->send('plugin.custom');
    }

    public static function update_instructions($attrib){

        $rcmail = \rcmail::get_instance();

        $attrib += ['id' => 'rcmresponseslist', 'tagname' => 'table'];

        $predefinedInstructions = $rcmail->user->get_prefs()['predefinedInstructions'] ?? [];

        error_log("predefinisane instrukcije" . print_r($predefinedInstructions, true));
        $instructionsArray = [];
        foreach ($predefinedInstructions as $instruction){
            $instructionsArray[] = ['id'=>$instruction['id'], 'name'=>$instruction['title']];
        }


        $plugin = [
            'list' => $instructionsArray,
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