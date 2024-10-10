<?php

namespace HercegDoo\AIComposePlugin\Actions\Settings;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;

class SaveInstruction extends AbstractAction
{
    /**
     * @param array<string, string> $args
     */
    protected function handler(array $args = []): void
    {
        $name = trim(\rcube_utils::get_input_string('_name', \rcube_utils::INPUT_POST));
        $text = trim(\rcube_utils::get_input_string('_text', \rcube_utils::INPUT_POST));

        $predefinedInstructions = $this->rcmail->user->get_prefs()['predefinedInstructions'] ?? [];

        $response = [
            'title' => $name,
            'message' => $text,
            'id' => uniqid(),
        ];

        if (empty($name) || empty($text)) {
            $this->rcmail->output->show_message('formincomplete', 'error');

            return;
        }

        $predefinedInstructions[] = $response;
        $this->rcmail->user->save_prefs(['predefinedInstructions' => $predefinedInstructions]);

        $this->rcmail->output->show_message('successfullysaved', 'confirmation');
        $this->rcmail->output->command('parent.updateinstructionlist', $response['id'], $response['title']);
        $this->rcmail->output->command('addinstructiontemplate', $response['id']);

        $this->rcmail->output->send('iframe');
    }

    protected function validate(): void
    {
        // TODO: Implement validate() method.
    }
}
