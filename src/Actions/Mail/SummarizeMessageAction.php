<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Actions\Mail;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;
use HercegDoo\AIComposePlugin\AIEmailService\Request;
use HercegDoo\AIComposePlugin\AIEmailService\Summary\MessageTextExtractor;
use HercegDoo\AIComposePlugin\AIEmailService\Summary\SummaryPromptBuilder;
use HercegDoo\AIComposePlugin\AIEmailService\Summary\SummaryProviderFactory;
use HercegDoo\AIComposePlugin\AIEmailService\Summary\SummaryService;

final class SummarizeMessageAction extends AbstractAction
{
    private const VIEW_PREVIEW = 'preview';
    private const VIEW_MESSAGE = 'message';

    protected function handler(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            if (!$this->rcmail->config->get('aiSummaryEnabled', true)
                || ($this->rcmail->user->get_prefs()['aicDefaults']['pluginVisibility'] ?? 'show') !== 'show') {
                throw new \RuntimeException('Summaries are disabled');
            }

            $uid = Request::postString('uid') ?? '';
            $folder = Request::postString('mailbox') ?? '';
            $refresh = Request::postString('refresh', '0');
            $view = Request::postString('view', self::VIEW_PREVIEW);
            if (!preg_match('/^[1-9][0-9]*(?:\.[0-9]+)*$/', $uid)
                || $folder === '' || \strlen($folder) > 1024 || preg_match('/[\x00-\x1F\x7F]/', $folder)
                || !\in_array($refresh, ['0', '1'], true)
                || !\in_array($view, [self::VIEW_PREVIEW, self::VIEW_MESSAGE], true)) {
                throw new \InvalidArgumentException('Invalid summary request');
            }

            $locale = $_SESSION['language'] ?? $this->rcmail->config->get('language', 'en_US');
            if (!\is_string($locale) || !preg_match('/^[a-z]{2,3}(?:_[A-Z]{2})?$/', $locale)) {
                $locale = 'en_US';
            }

            $message = new \rcube_message($uid, $folder);
            if (!$message->headers) {
                throw new \RuntimeException('Message unavailable');
            }

            [$provider, $config] = (new SummaryProviderFactory())->create($this->rcmail->config);
            $cache = $this->rcmail->get_cache('aicompose_summary', 'db', '7d');
            $extractor = new MessageTextExtractor();
            $body = $view === self::VIEW_MESSAGE ? $extractor->extract($message) : null;
            $sentenceCount = $body === null ? 1 : SummaryPromptBuilder::sentenceCountForBody($body);
            $cacheData = json_encode([
                SummaryPromptBuilder::VERSION,
                $view,
                $sentenceCount,
                $folder,
                $uid,
                $message->get_header('message-id'),
                $message->get_header('date'),
                $locale,
                \get_class($provider),
                $config,
            ], \JSON_THROW_ON_ERROR);
            $cacheKey = hash('sha256', $cacheData);
            if ($refresh !== '1' && $cache) {
                $saved = $cache->get($cacheKey);
                if (\is_array($saved) && isset($saved['sourceLanguage'], $saved['originalSummary'], $saved['translatedSummary'])) {
                    echo json_encode(['status' => 'success', 'targetLanguage' => $locale] + $saved);

                    return;
                }
            }

            if ($body === null) {
                $body = $extractor->extract($message);
            }
            if ($body === '' && trim($message->subject) === '') {
                throw new \RuntimeException('Message has no summarizable text');
            }

            $summary = (new SummaryService($provider, $config))->summarize($message->subject, $body, $locale, $sentenceCount, $view === self::VIEW_MESSAGE);
            if ($cache) {
                $cache->set($cacheKey, $summary);
            }

            echo json_encode(['status' => 'success', 'targetLanguage' => $locale] + $summary);
        } catch (\Throwable $error) {
            error_log('AIComposePlugin summary failed: ' . \get_class($error));
            echo json_encode(['status' => 'error']);
        }
    }
}
