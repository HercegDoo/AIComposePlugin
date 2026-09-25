<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Actions\Mail;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;
use HercegDoo\AIComposePlugin\AIEmailService\Request;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;

final class SaveComposeOptionsAction extends AbstractAction
{
    protected function handler(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $allowed = [
            'style' => array_values(Settings::getStyles()),
            'length' => array_values(Settings::getLengths()),
            'creativity' => Settings::getCreativities(),
        ];
        $updates = [];
        foreach (['style', 'length', 'creativity', 'language'] as $field) {
            if (!\array_key_exists($field, $_POST)) {
                continue;
            }

            $value = Request::postString($field);
            if ($field === 'language') {
                $value = $value !== null ? Settings::resolveLanguage($value) : null;
            } elseif (!\in_array($value, $allowed[$field], true)) {
                $value = null;
            }

            if ($value === null) {
                echo json_encode(['status' => 'error']);

                return;
            }

            $updates[$field] = $value;
        }

        if ($updates === []) {
            echo json_encode(['status' => 'error']);

            return;
        }

        $defaults = $this->rcmail->user->get_prefs()['aicDefaults'] ?? [];
        if (!\is_array($defaults)) {
            $defaults = [];
        }
        $saved = $this->rcmail->user->save_prefs(['aicDefaults' => array_merge($defaults, $updates)]);

        echo json_encode(['status' => $saved ? 'success' : 'error']);
    }
}
