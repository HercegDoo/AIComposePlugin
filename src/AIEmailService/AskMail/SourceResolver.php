<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\AskMail;

final class SourceResolver
{
    private \rcmail $rcmail;
    private string $tikaUrl;
    private int $maxMessageBytes;
    private int $maxAttachmentBytes;

    public function __construct(\rcmail $rcmail, string $tikaUrl, int $maxMessageBytes = 31457280, int $maxAttachmentBytes = 20971520)
    {
        $this->rcmail = $rcmail;
        $this->tikaUrl = rtrim($tikaUrl, '/');
        $this->maxMessageBytes = max(1, $maxMessageBytes);
        $this->maxAttachmentBytes = max(1, $maxAttachmentBytes);
    }

    /**
     * @param array<int, array<string, mixed>> $hits
     *
     * @return array<int, array<string, mixed>>
     */
    public function resolve(array $hits, string $question = ''): array
    {
        $sources = [];
        $perMessage = [];
        $validities = [];
        $messages = [];
        $texts = [];
        foreach ($hits as $hit) {
            if (\count($sources) >= 5) {
                break;
            }
            $folder = $hit['mailbox_s'] ?? null;
            $uid = $hit['uid_l'] ?? null;
            $partId = $hit['part_s'] ?? null;
            $offset = $hit['offset_i'] ?? null;
            if (!\is_string($folder) || $folder === '' || \strlen($folder) > 1024
                || preg_match('/[\x00-\x1F\x7F]/', $folder)
                || !is_numeric($uid) || (int) $uid < 1
                || !\is_string($partId) || !preg_match('/^[0-9]+(?:\.[0-9]+)*$/', $partId)
                || !is_numeric($offset) || (int) $offset < 0) {
                continue;
            }
            $identity = $folder . "\0" . $uid;
            if (($perMessage[$identity] ?? 0) >= 2) {
                continue;
            }
            try {
                if (!\array_key_exists($folder, $validities)) {
                    $data = $this->rcmail->storage->folder_data($folder);
                    $rawValidity = $data['UIDVALIDITY'] ?? null;
                    $validities[$folder] = is_numeric($rawValidity) ? (int) $rawValidity : 0;
                }
                $hitValidity = $hit['uidvalidity_l'] ?? null;
                if ($validities[$folder] < 1 || !is_numeric($hitValidity) || $validities[$folder] !== (int) $hitValidity) {
                    continue;
                }
                if (!\array_key_exists($identity, $messages)) {
                    $messages[$identity] = new \rcube_message((string) $uid, $folder);
                }
                $message = $messages[$identity];
                if (!$message->headers) {
                    continue;
                }
                $isFullText = ($hit['fts'] ?? false) === true;
                $part = $message->mime_parts[$partId] ?? null;
                $filename = !$isFullText && \is_string($part->filename ?? null) ? $part->filename : '';
                $textKey = $identity . "\0" . ($isFullText ? 'body' : $partId);
                if (\array_key_exists($textKey, $texts)) {
                    $text = $texts[$textKey];
                } else {
                    $text = $isFullText ? $this->bodyText($message) : $this->partText($message, $partId, $filename);
                    if (\strlen($text) <= 2097152) {
                        $texts[$textKey] = $text;
                    }
                }
                if ($text === '') {
                    continue;
                }
                $start = $isFullText ? self::findQuestionOffset($text, $question) : (int) $offset;
                $excerpt = trim((string) mb_substr($text, $start, 1800, 'UTF-8'));
                if ($excerpt === '') {
                    continue;
                }
                $perMessage[$identity] = ($perMessage[$identity] ?? 0) + 1;
                $sources[] = [
                    'number' => \count($sources) + 1,
                    'subject' => trim($message->subject) ?: '(no subject)',
                    'date' => (string) $message->get_header('date'),
                    'mailbox' => $folder,
                    'uid' => (string) $uid,
                    'filename' => $filename,
                    'excerpt' => $excerpt,
                ];
            } catch (\Throwable $error) {
                // A deleted message or unreadable part cannot support an answer.
                continue;
            }
        }

        return $sources;
    }

    private function partText(\rcube_message $message, string $partId, string $filename): string
    {
        $part = $message->mime_parts[$partId] ?? null;
        if ($part === null && $filename === '' && $partId === '1' && \is_string($message->body)) {
            return self::fallbackBody($message);
        }
        if ($part === null) {
            return '';
        }
        $mime = strtolower((string) ($part->mimetype ?? ''));
        if ($filename === '' || str_starts_with($mime, 'text/')) {
            $limit = $filename === '' ? $this->maxMessageBytes : $this->maxAttachmentBytes;
            $raw = $message->get_part_body($partId, true, $limit + 1);
            if (!\is_string($raw) || \strlen($raw) > $limit) {
                return '';
            }
            if ($mime === 'text/html') {
                $raw = (string) preg_replace('/<\s*(script|style)\b[^>]*>.*?<\s*\/\s*\1\s*>/is', ' ', $raw);
                $raw = (string) preg_replace('/<\s*\/?(?:p|div|br|li|tr)\b[^>]*>/i', ' ', $raw);
                $raw = html_entity_decode(strip_tags($raw), \ENT_QUOTES | \ENT_HTML5, 'UTF-8');
            }

            return self::normalize($raw);
        }
        if ($this->tikaUrl === '' || !\in_array(parse_url($this->tikaUrl, \PHP_URL_SCHEME), ['http', 'https'], true)) {
            return '';
        }
        $raw = $message->get_part_body($partId, false, $this->maxAttachmentBytes + 1);
        if (!\is_string($raw) || \strlen($raw) > $this->maxAttachmentBytes) {
            return '';
        }
        $handle = curl_init($this->tikaUrl . '/tika');
        if ($handle === false) {
            return '';
        }
        curl_setopt_array($handle, [
            \CURLOPT_CUSTOMREQUEST => 'PUT',
            \CURLOPT_POSTFIELDS => $raw,
            \CURLOPT_HTTPHEADER => ['Accept: text/plain', 'Content-Type: ' . $mime],
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_CONNECTTIMEOUT => 5,
            \CURLOPT_TIMEOUT => 60,
            \CURLOPT_SSL_VERIFYPEER => true,
            \CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        try {
            $result = curl_exec($handle);
            $status = curl_getinfo($handle, \CURLINFO_HTTP_CODE);
        } finally {
            curl_close($handle);
        }

        return \is_string($result) && $status >= 200 && $status < 300 ? self::normalize($result) : '';
    }

    private function bodyText(\rcube_message $message): string
    {
        $plain = null;
        $html = null;
        foreach ($message->parts as $part) {
            if (($part->type ?? null) !== 'content' || ($part->ctype_primary ?? null) !== 'text') {
                continue;
            }
            if (($part->ctype_secondary ?? null) === 'plain' && $plain === null) {
                $plain = $part;
            } elseif (($part->ctype_secondary ?? null) === 'html' && $html === null) {
                $html = $part;
            }
        }
        $selected = $plain ?? $html;
        if ($selected === null) {
            return self::fallbackBody($message);
        }

        return $this->partText($message, (string) $selected->mime_id, '');
    }

    private static function findQuestionOffset(string $text, string $question): int
    {
        preg_match_all('/TEXT "([^"]+)"/u', RoundcubeFtsSearch::criteria($question), $matches);
        foreach ($matches[1] as $term) {
            $position = mb_stripos($text, $term, 0, 'UTF-8');
            if ($position !== false) {
                return max(0, $position - 300);
            }
        }

        return 0;
    }

    private static function fallbackBody(\rcube_message $message): string
    {
        if (!\is_string($message->body)) {
            return '';
        }
        $body = $message->body;
        if (!empty($message->headers->charset)) {
            $body = \rcube_charset::convert($body, $message->headers->charset);
        }

        return self::normalize($body);
    }

    private static function normalize(string $text): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
