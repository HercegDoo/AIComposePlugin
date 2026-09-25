<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Actions\Mail;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;
use HercegDoo\AIComposePlugin\AIEmailService\Request;
use HercegDoo\AIComposePlugin\Utilities\ReplySuggestionStore;

final class PrepareSuggestedReplyAction extends AbstractAction
{
    protected function handler(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            if (!$this->rcmail->config->get('aiSummaryEnabled', true)
                || ($this->rcmail->user->get_prefs()['aicDefaults']['pluginVisibility'] ?? 'show') !== 'show') {
                throw new \RuntimeException('Suggestions are disabled');
            }

            $uid = Request::postString('uid') ?? '';
            $mailbox = Request::postString('mailbox') ?? '';
            $instruction = trim(Request::postString('instruction') ?? '');
            $language = trim(Request::postString('language') ?? '');
            if (!preg_match('/^[1-9][0-9]*(?:\.[0-9]+)*$/', $uid)
                || $mailbox === '' || \strlen($mailbox) > 1024 || preg_match('/[\x00-\x1F\x7F]/', $mailbox)
                || $instruction === '' || \strlen($instruction) > 1200
                || $language === '' || \strlen($language) > 60) {
                throw new \InvalidArgumentException('Invalid reply suggestion');
            }

            $token = ReplySuggestionStore::save($uid, $mailbox, $instruction, $language);
            echo json_encode(['status' => 'success', 'token' => $token]);
        } catch (\Throwable $error) {
            error_log('AIComposePlugin reply preparation failed: ' . \get_class($error));
            echo json_encode(['status' => 'error']);
        }
    }
}
