<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Providers;

use Curl\Curl;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\EmailPrompt;

final class Ollama implements CompletionProviderInterface
{
    private Curl $curl;

    public function __construct(?Curl $curl = null)
    {
        $this->curl = $curl ?? new Curl();
    }

    /**
     * @param array<string, mixed> $config
     *
     * @throws ProviderException
     */
    public function complete(EmailPrompt $prompt, array $config): string
    {
        $model = $config['model'] ?? '';
        $url = $config['url'] ?? 'http://127.0.0.1:11434/api/chat';
        if (!\is_string($model) || $model === '' || !\is_string($url) || !\in_array(parse_url($url, \PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw new ProviderException('Invalid Ollama summary configuration');
        }

        $this->curl->setHeader('Content-Type', 'application/json');
        $this->curl->setOpts([\CURLOPT_TIMEOUT => 60]);

        try {
            $response = $this->curl->post($url, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $prompt->getSystemInstruction()],
                    ['role' => 'user', 'content' => $prompt->getUserInstruction()],
                ],
                'format' => 'json',
                'stream' => false,
                'options' => ['temperature' => 0, 'num_predict' => 450],
            ]);
        } catch (\Throwable $e) {
            throw new ProviderException('Ollama request failed');
        }

        if ($this->curl->error) {
            throw new ProviderException('Ollama request failed');
        }

        $content = \is_object($response) && isset($response->message) && \is_object($response->message)
            ? ($response->message->content ?? null)
            : null;
        if (!\is_string($content) || trim($content) === '') {
            throw new ProviderException('No Ollama summary content found');
        }

        return $content;
    }
}
