<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Summary;

use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\CompletionProviderInterface;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\Ollama;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\OpenAI;

final class SummaryProviderFactory
{
    /**
     * @return array{0: CompletionProviderInterface, 1: array<string, mixed>}
     */
    public function create(\rcube_config $settings): array
    {
        $name = $settings->get('aiSummaryProvider', 'OpenAI');
        if ($name === 'Ollama') {
            $config = $settings->get('aiSummaryOllamaConfig', []);
            if (!\is_array($config)) {
                throw new ProviderException('Invalid Ollama summary configuration');
            }

            return [new Ollama(), $config];
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

            return [new OpenAI(), $config];
        }

        throw new ProviderException('Unsupported summary provider');
    }
}
