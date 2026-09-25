<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Actions\Mail;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;
use HercegDoo\AIComposePlugin\AIEmailService\Request;
use HercegDoo\AIComposePlugin\AIEmailService\Summary\SummaryProviderFactory;
use HercegDoo\AIComposePlugin\AIEmailService\Translation\MessageTranslationText;
use HercegDoo\AIComposePlugin\AIEmailService\Translation\TranslationService;

final class TranslateMessageAction extends AbstractAction
{
    protected function handler(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $defaults = $this->rcmail->user->get_prefs()['aicDefaults'] ?? [];
            if (!\is_array($defaults) || ($defaults['pluginVisibility'] ?? 'show') !== 'show'
                || !$this->rcmail->config->get('aiTranslationEnabled', true)) {
                throw new \RuntimeException('Translation is disabled');
            }

            $uid = Request::postString('uid') ?? '';
            $folder = Request::postString('mailbox') ?? '';
            $locale = Request::postString('locale') ?? '';
            $index = Request::postString('index') ?? '';
            if (!preg_match('/^[1-9][0-9]*(?:\.[0-9]+)*$/', $uid)
                || $folder === '' || \strlen($folder) > 1024 || preg_match('/[\x00-\x1F\x7F]/', $folder)
                || !preg_match('/^(0|[1-9][0-9]{0,2})$/', $index)
                || $locale === '' || \strlen($locale) > 32
                || !isset($this->rcmail->list_languages()[$locale])) {
                throw new \InvalidArgumentException('Invalid translation request');
            }

            $message = new \rcube_message($uid, $folder);
            if (!$message->headers) {
                throw new \RuntimeException('Message unavailable');
            }

            $extractor = new MessageTranslationText();
            $body = $extractor->extract($message);
            $subject = trim((string) $message->subject);
            $source = ($subject !== '' ? 'Subject: ' . $subject . "\n\n" : '') . $body;
            if ($source === '') {
                throw new \RuntimeException('Message has no translatable text');
            }
            if (mb_strlen($source, 'UTF-8') > MessageTranslationText::MAX_CHARACTERS) {
                throw new \LengthException('Message is too large to translate');
            }
            $chunks = $extractor->chunks($source);
            $position = (int) $index;
            if (!isset($chunks[$position])) {
                throw new \InvalidArgumentException('Invalid translation part');
            }

            [$provider, $config] = (new SummaryProviderFactory())->create($this->rcmail->config);
            $translated = (new TranslationService($provider, $config))->translate($chunks[$position], $locale);
            echo json_encode([
                'status' => 'success',
                'index' => $position,
                'totalChunks' => \count($chunks),
                'sourceHash' => hash('sha256', $source),
                'translation' => $translated,
            ], \JSON_THROW_ON_ERROR);
        } catch (\LengthException $error) {
            echo json_encode(['status' => 'error', 'code' => 'too_large']);
        } catch (\Throwable $error) {
            error_log('AIComposePlugin translation failed: ' . \get_class($error));
            echo json_encode(['status' => 'error']);
        }
    }
}
