<?php

namespace HercegDoo\AIComposePlugin\Actions;

class GetInstructionsAction extends AbstractAction
{
    private array $predefinedInstructions;

    protected function handler(): void
    {
        header('Content-Type: application/json');

        echo json_encode([
            'status' => 'success',
            'returnValue' => $this->predefinedInstructions,
        ]);
    }

    protected function validate(): void
    {
        $this->predefinedInstructions = $this->rcmail->user->get_prefs()['predefinedInstructions'] ?? [];
    }
}
