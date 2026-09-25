<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Style;

final class SentStyleTextExtractor
{
    private const MAX_BODY_BYTES = 16000;
    private const MAX_EXAMPLE_CHARS = 1200;

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
        $body = $part ? $message->get_part_body($part->mime_id, true, self::MAX_BODY_BYTES) : ($message->body ?? '');
        if (!\is_string($body)) {
            return '';
        }
        if (!$part && !empty($message->headers->charset)) {
            $body = \rcube_charset::convert($body, $message->headers->charset);
        }

        if ($part && ($part->ctype_secondary ?? null) === 'html') {
            $body = (string) preg_replace('/<\s*(?:blockquote|div\b[^>]*(?:gmail_quote|_rc_sig|signature))\b[^>]*>.*$/is', '', $body);
            $body = (string) preg_replace('/<\s*(script|style)\b[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $body);
            $body = (string) preg_replace('/<\s*(?:br|\/p|\/div|\/li|\/tr|\/h[1-6])\b[^>]*>/i', "\n", $body);
            $body = html_entity_decode(strip_tags($body), \ENT_QUOTES | \ENT_HTML5, 'UTF-8');
        }

        $lines = preg_split('/\R/u', $body) ?: [];
        $kept = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^(?:>+|--\s*$|[-_]{2,}\s*(?:original|forwarded|izvorna)|(?:from|od|von)\s*:|(?:on|am|le|el) .{1,160} (?:wrote|schrieb|a écrit|escribió)\s*:|.{1,160}\bje napisao(?:\/la)?\s*:)/iu', $line)
                || ($kept !== [] && preg_match('/^(?:lijep(?:i)? pozdrav(?:i)?|lep pozdrav|srdač(?:an|ni) pozdrav(?:i)?|s poštovanjem|best regards|kind regards|warm regards|sincerely|mit freundlichen grüßen|freundliche grüße|cordialement|saludos cordiales|cordiali saluti)[,.!\s]*$/iu', $line))) {
                break;
            }
            $kept[] = $line;
        }

        $body = trim((string) preg_replace('/\n{3,}/', "\n\n", implode("\n", $kept)));
        preg_match('/^.{0,' . self::MAX_EXAMPLE_CHARS . '}/us', $body, $match);

        return trim($match[0] ?? '');
    }
}
