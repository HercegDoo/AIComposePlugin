<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Actions\Mail;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;
use HercegDoo\AIComposePlugin\AIEmailService\AskMail\AnswerService;
use HercegDoo\AIComposePlugin\AIEmailService\AskMail\RoundcubeFtsSearch;
use HercegDoo\AIComposePlugin\AIEmailService\AskMail\SearchGateway;
use HercegDoo\AIComposePlugin\AIEmailService\AskMail\SourceResolver;
use HercegDoo\AIComposePlugin\AIEmailService\Request;
use HercegDoo\AIComposePlugin\AIEmailService\Summary\SummaryProviderFactory;

final class AskMailAction extends AbstractAction
{
    protected function handler(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        try {
            if ($this->rcmail->config->get('aiAskMailEnabled', false) !== true
                || ($this->rcmail->user->get_prefs()['aicDefaults']['pluginVisibility'] ?? 'show') !== 'show') {
                throw new \RuntimeException('Ask Mail is disabled');
            }
            $question = trim(Request::postString('question') ?? '');
            if ($question === '' || \strlen($question) > 1000 || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $question)) {
                throw new \InvalidArgumentException('Invalid question');
            }
            $config = $this->rcmail->config->get('aiAskMailConfig', []);
            if (!\is_array($config)) {
                throw new \RuntimeException('Invalid Ask Mail configuration');
            }
            $search = new SearchGateway($config);
            $owner = (int) $this->rcmail->user->ID;
            $fullTextHits = (new RoundcubeFtsSearch($this->rcmail))->search($question);
            $matches = $search->search($owner, $question, $fullTextHits);
            try {
                $progress = $search->progress($owner);
            } catch (\Throwable $error) {
                $progress = ['indexed' => 0, 'skipped' => 0, 'estimated' => 0, 'complete' => false, 'error' => ''];
            }
            $sources = (new SourceResolver(
                $this->rcmail,
                \is_string($config['tikaUrl'] ?? null) ? $config['tikaUrl'] : '',
                \is_int($config['maxMessageBytes'] ?? null) ? $config['maxMessageBytes'] : 31457280,
                \is_int($config['maxAttachmentBytes'] ?? null) ? $config['maxAttachmentBytes'] : 20971520
            ))->resolve($matches['hits'], $question);
            if ($sources === []) {
                echo json_encode(['status' => 'empty', 'sources' => [], 'progress' => $progress, 'mode' => $matches['mode']]);

                return;
            }
            try {
                [$provider, $providerConfig] = (new SummaryProviderFactory())->create($this->rcmail->config);
                $answer = (new AnswerService($provider, $providerConfig))->answer($question, $sources);
                echo json_encode(['status' => 'success', 'sources' => $sources, 'progress' => $progress, 'mode' => $matches['mode']] + $answer);
            } catch (\Throwable $error) {
                error_log('AIComposePlugin Ask Mail answer failed: ' . \get_class($error));
                echo json_encode(['status' => 'sources', 'sources' => $sources, 'progress' => $progress, 'mode' => $matches['mode']]);
            }
        } catch (\Throwable $error) {
            error_log('AIComposePlugin Ask Mail failed: ' . \get_class($error));
            echo json_encode(['status' => 'error']);
        }
    }
}
