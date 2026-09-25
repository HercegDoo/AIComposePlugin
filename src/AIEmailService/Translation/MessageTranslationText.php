<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Translation;

final class MessageTranslationText
{
    public const MAX_CHARACTERS = 60000;
    public const CHUNK_CHARACTERS = 3500;
    private const MAX_RAW_BYTES = 1000001;

    public function extract(\rcube_message $message): string
    {
        $plainPart = null;
        $htmlPart = null;
        foreach ($message->parts as $part) {
            if (($part->type ?? null) !== 'content' || ($part->ctype_primary ?? null) !== 'text') {
                continue;
            }
            if (($part->ctype_secondary ?? null) === 'plain' && $plainPart === null) {
                $plainPart = $part;
            } elseif (($part->ctype_secondary ?? null) === 'html' && $htmlPart === null) {
                $htmlPart = $part;
            }
        }

        $part = $plainPart ?? $htmlPart;
        if ($part && isset($part->size) && is_numeric($part->size) && $part->size >= self::MAX_RAW_BYTES) {
            throw new \LengthException('Message is too large to translate');
        }
        $body = $part ? $message->get_part_body($part->mime_id, true) : ($message->body ?? '');
        if (!\is_string($body) || \strlen($body) >= self::MAX_RAW_BYTES) {
            throw new \LengthException('Message is too large to translate');
        }
        if (!$part && !empty($message->headers->charset)) {
            $body = \rcube_charset::convert($body, $message->headers->charset);
        }
        if ($part && ($part->ctype_secondary ?? null) === 'html') {
            $body = (new \rcube_html2text($body, false, \rcube_html2text::LINKS_INLINE, 0))->get_text();
        }

        $body = trim((string) preg_replace('/\r\n?|\x{2028}|\x{2029}/u', "\n", $body));
        $body = (string) preg_replace('/[\t ]+/u', ' ', $body);
        $body = (string) preg_replace('/\n{4,}/u', "\n\n\n", $body);
        if (mb_strlen($body, 'UTF-8') > self::MAX_CHARACTERS) {
            throw new \LengthException('Message is too large to translate');
        }

        return $body;
    }

    /** @return string[] */
    public function chunks(string $body): array
    {
        $chunks = [];
        while ($body !== '') {
            if (mb_strlen($body, 'UTF-8') <= self::CHUNK_CHARACTERS) {
                $chunks[] = $body;
                break;
            }

            $slice = mb_substr($body, 0, self::CHUNK_CHARACTERS, 'UTF-8');
            $break = max((int) mb_strrpos($slice, "\n", 0, 'UTF-8'), (int) mb_strrpos($slice, ' ', 0, 'UTF-8'));
            $length = $break > self::CHUNK_CHARACTERS / 2 ? $break + 1 : self::CHUNK_CHARACTERS;
            $chunks[] = mb_substr($body, 0, $length, 'UTF-8');
            $body = mb_substr($body, $length, null, 'UTF-8');
        }

        return $chunks;
    }
}
