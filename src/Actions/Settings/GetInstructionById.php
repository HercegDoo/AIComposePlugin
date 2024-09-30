<?php

namespace HercegDoo\AIComposePlugin\Actions\Settings;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;

class GetInstructionById extends AbstractAction
{
    private array $predefinedInstructions;

    protected function handler(): void
    {
        header('Content-Type: application/json');

        $id = \rcube_utils::get_input_value('id', \rcube_utils::INPUT_GET);

        $return = $id ? array_values(array_filter($this->predefinedInstructions, static fn ($msg) => $msg['id'] === $id))[0] ?? [] : [];

        echo json_encode([
            'status' => 'success',
            'returnValue' => $return,
        ]);
    }

    protected function validate(): void
    {
        $this->predefinedInstructions = \rcmail::get_instance()->user->get_prefs()['predefinedInstructions'] ?? [];
    }
}
