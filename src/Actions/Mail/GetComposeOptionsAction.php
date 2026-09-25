<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Actions\Mail;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;

final class GetComposeOptionsAction extends AbstractAction
{
    protected function handler(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: private, no-store');

        $defaults = $this->rcmail->user->get_prefs()['aicDefaults'] ?? [];
        if (!\is_array($defaults)) {
            $defaults = [];
        }

        $options = [];
        foreach (['style', 'length', 'creativity', 'language'] as $field) {
            if (isset($defaults[$field]) && \is_string($defaults[$field])) {
                $options[$field] = $defaults[$field];
            }
        }

        echo json_encode(['status' => 'success', 'options' => $options]);
    }
}
