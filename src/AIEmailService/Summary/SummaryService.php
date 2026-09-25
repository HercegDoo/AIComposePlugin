<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Summary;

use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\CompletionProviderInterface;

final class SummaryService
{
    private CompletionProviderInterface $provider;

    /** @var array<string, mixed> */
    private array $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(CompletionProviderInterface $provider, array $config)
    {
        $this->provider = $provider;
        $this->config = $config;
    }

    /**
     * @return array{sourceLanguage: string, originalSummary: string, translatedSummary: string, replySuggestions: array<int, array{label: string, instruction: string}>}
     */
    public function summarize(string $subject, string $body, string $targetLocale, int $sentenceCount = 1, bool $includeReplySuggestions = false): array
    {
        $prompt = (new SummaryPromptBuilder())->build($subject, $body, $targetLocale, $sentenceCount, $includeReplySuggestions);
        $raw = trim($this->provider->complete($prompt, $this->config));
        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');
        if ($start === false || $end === false || $end < $start) {
            throw new ProviderException('Invalid summary response');
        }

        $data = json_decode(substr($raw, $start, $end - $start + 1), true);
        if (!\is_array($data)) {
            throw new ProviderException('Invalid summary response');
        }

        $sourceLanguage = $this->clean($data['source_language'] ?? null, 60);
        $summaryLimit = $sentenceCount > 1 ? 900 : 700;
        $originalSummary = $this->clean($data['original_summary'] ?? null, $summaryLimit);
        $translatedSummary = $this->clean($data['translated_summary'] ?? null, $summaryLimit);
        if ($sourceLanguage === '' || $originalSummary === '' || $translatedSummary === '') {
            throw new ProviderException('Incomplete summary response');
        }

        return [
            'sourceLanguage' => $sourceLanguage,
            'originalSummary' => $originalSummary,
            'translatedSummary' => $translatedSummary,
            'replySuggestions' => $includeReplySuggestions && ($data['reply_intent_clear'] ?? null) === true
                ? $this->replySuggestions($data['reply_suggestions'] ?? null) : [],
        ];
    }

    /**
     * @phpstan-param mixed $value
     *
     * @return array<int, array{label: string, instruction: string}>
     */
    private function replySuggestions($value): array
    {
        if (!\is_array($value)) {
            return [];
        }

        $suggestions = [];
        foreach (\array_slice($value, 0, 3) as $item) {
            if (!\is_array($item)) {
                continue;
            }
            $label = $this->clean($item['label'] ?? null, 80);
            $instruction = $this->clean($item['instruction'] ?? null, 300);
            if ($label === '' || $instruction === '') {
                continue;
            }
            $suggestions[] = ['label' => $label, 'instruction' => $instruction];
        }

        return $suggestions;
    }

    /** @phpstan-param mixed $value */
    private function clean($value, int $limit): string
    {
        if (!\is_string($value)) {
            return '';
        }

        $value = (string) preg_replace('/<\s*(script|style)\b[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $value);
        $value = html_entity_decode(strip_tags($value), \ENT_QUOTES | \ENT_HTML5, 'UTF-8');
        $value = trim((string) preg_replace('/[\s\x00-\x1F\x7F]+/u', ' ', $value));
        preg_match('/^.{0,' . $limit . '}/us', $value, $match);

        return trim($match[0] ?? '');
    }
}
