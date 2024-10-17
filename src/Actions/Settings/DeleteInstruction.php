<?php

namespace HercegDoo\AIComposePlugin\Actions\Settings;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;
use HercegDoo\AIComposePlugin\Actions\SkipValidationInterface;

class DeleteInstruction extends AbstractAction implements SkipValidationInterface
{
    protected function handler(): void
    {
        $idToRemove = \rcube_utils::get_input_string('_id', \rcube_utils::INPUT_POST);
        $predefinedInstructions = $this->rcmail->user->get_prefs()['predefinedInstructions'] ?? [];

        if ($idToRemove) {
            $updatedPredefinedInstructions = array_filter($predefinedInstructions, static function ($predefinedInstruction) use ($idToRemove) {
                return !str_contains($idToRemove, $predefinedInstruction['id']);
            });

            $updatedPredefinedInstructions = array_values($updatedPredefinedInstructions);

            $this->rcmail->user->save_prefs(['predefinedInstructions' => $updatedPredefinedInstructions]);
            $this->rcmail->output->command('display_message', $this->translation('ai_predefined_successful_delete'), 'confirmation');
            $this->rcmail->output->command('deleteinstruction', $idToRemove);
        }
        $this->rcmail->output->send();
    }

    protected function validate(): void
    {
    }
}
