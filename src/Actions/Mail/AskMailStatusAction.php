<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Actions\Mail;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;
use HercegDoo\AIComposePlugin\AIEmailService\AskMail\SearchGateway;

final class AskMailStatusAction extends AbstractAction
{
    protected function handler(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        try {
            if ($this->rcmail->config->get('aiAskMailEnabled', false) !== true
                || ($this->rcmail->user->get_prefs()['aicDefaults']['pluginVisibility'] ?? 'show') !== 'show') {
                throw new \RuntimeException('Ask Mail is disabled');
            }
            $config = $this->rcmail->config->get('aiAskMailConfig', []);
            if (!\is_array($config)) {
                throw new \RuntimeException('Invalid Ask Mail configuration');
            }
            $progress = (new SearchGateway($config))->progress((int) $this->rcmail->user->ID);
            echo json_encode(['status' => 'success', 'progress' => $progress]);
        } catch (\Throwable $error) {
            echo json_encode(['status' => 'error']);
        }
    }
}
