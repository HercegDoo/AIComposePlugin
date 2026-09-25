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
        $postedData = \rcube_utils::get_input_value('data', \rcube_utils::INPUT_POST);
        $formOptions = \is_array($postedData) && isset($postedData['aic']) && \is_array($postedData['aic'])
            ? $postedData['aic']
            : null;
        $input = $formOptions ?? $_POST;
        $updates = [];
        foreach (['style', 'length', 'creativity', 'language'] as $field) {
            if (!\array_key_exists($field, $input)) {
                continue;
            }

            $value = $formOptions !== null ? $formOptions[$field] : Request::postString($field);
            if (!\is_string($value)) {
                echo json_encode(['status' => 'error']);

                return;
            }
            if ($field === 'language') {
                $value = Settings::resolveLanguage($value);
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
