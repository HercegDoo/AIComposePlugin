<?php

namespace HercegDoo\AIComposePlugin\Actions\Settings;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;

class DeleteInstruction extends AbstractAction
{

    protected function handler(): void
    {
        $idToRemove = \rcube_utils::get_input_string('_id', \rcube_utils::INPUT_POST);
        error_log("Id za obrisati: " . print_r($idToRemove, true));
        $predefinedInstructions = $this->rcmail->user->get_prefs()['predefinedInstructions'] ?? [];

        $updatedPredefinedInstructions = array_filter($predefinedInstructions, function($predefinedInstruction) use ($idToRemove){

            return strpos($idToRemove, $predefinedInstruction['id']) === false;

        });


        $this->rcmail->user->save_prefs(['predefinedInstructions' => $updatedPredefinedInstructions]);
        error_log('Updated Predefined Instructions ' . print_r($updatedPredefinedInstructions, true));
        $this->rcmail->output->command('display_message', "E obriso si ga", 'confirmation');
        $this->rcmail->output->command('deleteinstruction', $idToRemove);
        $this->rcmail->output->send('AIComposePlugin.basepredefinedinstructions');
    }

    protected function validate(): void
    {
        // TODO: Implement validate() method.
    }
}