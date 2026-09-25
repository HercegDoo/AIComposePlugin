<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Summary;

use HercegDoo\AIComposePlugin\AIEmailService\Debug\RequestLogger;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\CompletionProviderInterface;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\Gemini;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\Ollama;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\OpenAI;

final class SummaryProviderFactory
{
    /**
     * @return array{0: CompletionProviderInterface, 1: array<string, mixed>}
     */
    public function create(\rcube_config $settings): array
    {
        $debugEnabled = $settings->get('aiDebugLogging', false) === true;
        $requestLogger = new RequestLogger(
            $debugEnabled,
            $debugEnabled ? (string) \rcmail::get_instance()->user->ID : null
        );
        $name = $settings->get('aiSummaryProvider', 'OpenAI');
        if ($name === 'Ollama') {
            $config = $settings->get('aiSummaryOllamaConfig', []);
            if (!\is_array($config)) {
                throw new ProviderException('Invalid Ollama summary configuration');
            }

            return [new Ollama(null, $requestLogger), $config];
        }

        if ($name === 'OpenAI') {
            $base = $settings->get('aiProviderOpenAIConfig', []);
            $overrides = $settings->get('aiSummaryOpenAIConfig', []);
            if (!\is_array($base) || !\is_array($overrides)) {
                throw new ProviderException('Invalid OpenAI summary configuration');
            }
            $config = array_merge($base, $overrides);
            $config['maxTokens'] = $config['maxTokens'] ?? 1200;
            $config['temperature'] = 0;
            if (empty($config['apiKey']) || empty($config['model'])) {
                throw new ProviderException('Missing OpenAI summary credentials or model');
            }

            return [new OpenAI(null, $requestLogger), $config];
        }

        if ($name === 'Gemini') {
            $base = $settings->get('aiProviderGeminiConfig', []);
            $overrides = $settings->get('aiSummaryGeminiConfig', []);
            if (!\is_array($base) || !\is_array($overrides)) {
                throw new ProviderException('Invalid Gemini summary configuration');
            }
            $config = array_merge($base, $overrides);
            $config['maxTokens'] = $config['maxTokens'] ?? 1200;
            $config['temperature'] = 0;
            if (empty($config['apiKey']) || empty($config['model'])) {
                throw new ProviderException('Missing Gemini summary credentials or model');
            }

            return [new Gemini(null, $requestLogger), $config];
        }

        throw new ProviderException('Unsupported summary provider');
    }
}
