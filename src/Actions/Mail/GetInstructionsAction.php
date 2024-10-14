<?php

namespace HercegDoo\AIComposePlugin\Actions\Mail;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;
use HercegDoo\AIComposePlugin\Actions\SkipValidationInterface;

class GetInstructionsAction extends AbstractAction implements SkipValidationInterface
{
    protected function handler(): void
    {
        $predefinedInstructions = $this->rcmail->user->get_prefs()['predefinedInstructions'] ?? [];
        header('Content-Type: application/json');

        echo json_encode([
            'predefinedInstructions' => (array) $predefinedInstructions,
            'status' => 'success',
        ]);
    }

    protected function validate(): void
    {
    }
}
