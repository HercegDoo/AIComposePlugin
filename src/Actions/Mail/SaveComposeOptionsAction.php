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

        $style = Request::postString('style');
        $length = Request::postString('length');
        $creativity = Request::postString('creativity');
        $language = Request::postString('language');
        $resolvedLanguage = $language !== null ? Settings::resolveLanguage($language) : null;

        if (!\in_array($style, array_values(Settings::getStyles()), true)
            || !\in_array($length, array_values(Settings::getLengths()), true)
            || !\in_array($creativity, Settings::getCreativities(), true)
            || $resolvedLanguage === null) {
            echo json_encode(['status' => 'error']);

            return;
        }

        $defaults = $this->rcmail->user->get_prefs()['aicDefaults'] ?? [];
        if (!\is_array($defaults)) {
            $defaults = [];
        }
        $saved = $this->rcmail->user->save_prefs(['aicDefaults' => array_merge($defaults, [
            'style' => $style,
            'length' => $length,
            'creativity' => $creativity,
            'language' => $resolvedLanguage,
        ])]);

        echo json_encode(['status' => $saved ? 'success' : 'error']);
    }
}
