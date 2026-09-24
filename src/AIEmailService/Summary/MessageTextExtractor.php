<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Summary;

final class MessageTextExtractor
{
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
        $body = $part ? $message->get_part_body($part->mime_id, true, 30000) : ($message->body ?? '');
        if (!\is_string($body)) {
            return '';
        }
        if (!$part && !empty($message->headers->charset)) {
            $body = \rcube_charset::convert($body, $message->headers->charset);
        }
        if ($part && ($part->ctype_secondary ?? null) === 'html') {
            $body = (string) preg_replace('/<\s*(script|style)\b[^>]*>.*?<\s*\/\s*\1\s*>/is', ' ', $body);
            $body = (string) preg_replace('/<[^>]*>/', ' ', $body);
            $body = html_entity_decode($body, \ENT_QUOTES | \ENT_HTML5, 'UTF-8');
        }

        $body = trim((string) preg_replace('/\s+/u', ' ', $body));
        preg_match('/^.{0,12000}/us', $body, $match);

        return trim($match[0] ?? '');
    }
}
