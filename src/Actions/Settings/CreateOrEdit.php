<?php

namespace HercegDoo\AIComposePlugin\Actions\Settings;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;

class CreateOrEdit extends AbstractAction
{
    private array $predefinedInstructions;

    protected function handler(): void
    {
        $rcmail = \rcmail::get_instance();

        self::$plugin->include_script('assets/dist/settings.bundle.js');
        $maxPredefinedMessages = $rcmail->config->get('aiMaxPredefinedInstructions');

        $found = false;
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
            foreach ($this->predefinedInstructions as &$instruction) {
                if ($instruction['id'] === $id) {
                    $instruction['title'] = htmlspecialchars($_POST['title'], \ENT_QUOTES, 'UTF-8');
                    $instruction['value'] = htmlspecialchars($_POST['value'], \ENT_QUOTES, 'UTF-8');
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            if (\count($this->predefinedInstructions) >= $maxPredefinedMessages) {
                echo json_encode([
                    'status' => 'error',
                    'message' => $this->translation('ai_predefined_max_instructions_error'),
                ]);
                exit();
            }
            $predefinedInstruction = [
                'title' => htmlspecialchars($_POST['title'], \ENT_QUOTES, 'UTF-8'),
                'value' => htmlspecialchars($_POST['value'], \ENT_QUOTES, 'UTF-8'),
                'id' => uniqid('predefined-instruction-'),
            ];
            $this->predefinedInstructions[] = $predefinedInstruction;
        }

        $rcmail->user->save_prefs(['predefinedInstructions' => $this->predefinedInstructions]);

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'returnValue' => $this->predefinedInstructions,
        ]);
    }

    protected function validate(): void
    {
        $this->predefinedInstructions = \rcmail::get_instance()->user->get_prefs()['predefinedInstructions'] ?? [];
    }
}
