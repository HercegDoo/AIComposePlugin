<?php

namespace HercegDoo\AIComposePlugin\Actions\Settings;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;

class SaveInstruction extends AbstractAction
{
    protected function handler(): void
    {
        error_log('Uso u handler saveinstruction');
        $rcmail = \rcmail::get_instance();

        // Uzimanje podataka iz POST zahtjeva
        $name = trim(\rcube_utils::get_input_string('_name', \rcube_utils::INPUT_POST));
        $text = trim(\rcube_utils::get_input_string('_text', \rcube_utils::INPUT_POST));

        $predefinedInstructions = $rcmail->user->get_prefs()['predefinedInstructions'] ?? [];

        // Pravljenje asocijativnog niza
        $response = [
            'title' => $name,
            'message' => $text,
            'id' => uniqid(),
        ];

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
        $rcmail->output->add_handler('responseform', [$this, 'response_form']);
        $rcmail->output->send('AIComposePlugin.instructionedit');
    }

    protected function validate(): void
    {
        // TODO: Implement validate() method.
    }
}
