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
        $id = trim(\rcube_utils::get_input_string('_id', \rcube_utils::INPUT_POST));
        $predefinedInstructions = $this->rcmail->user->get_prefs()['predefinedInstructions'] ?? [];

        if (empty($name) || empty($text)) {
            $this->rcmail->output->show_message($this->translation('ai_predefined_invalid_input'), 'error');
            $this->rcmail->output->command('addinstructiontemplate');
            $this->rcmail->output->send('iframe');
        }

        if (!empty($id)) {
            foreach ($predefinedInstructions as &$predefinedInstruction) {
                if (str_contains($id, $predefinedInstruction['id'])) {
                    $predefinedInstruction['title'] = $name;
                    $predefinedInstruction['message'] = $text;
                }
            }
            unset($predefinedInstruction);
        } else {
            $response = [
                'title' => $name,
                'message' => $text,
                'id' => uniqid(),
            ];

            $id = $response['id'];

            $predefinedInstructions[] = $response;
        }

        $this->rcmail->output->show_message($this->translation('ai_predefined_successful_save'), 'confirmation');
        $this->rcmail->output->command('parent.updateinstructionlist', $id, $name);
        $this->rcmail->output->command('addinstructiontemplate', $id);
        $this->rcmail->user->save_prefs(['predefinedInstructions' => $predefinedInstructions]);
        $this->rcmail->output->send('iframe');
    }

    protected function validate(): void
    {
        // TODO: Implement validate() method.
    }
}
