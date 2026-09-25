<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Providers;

use Curl\Curl;
use HercegDoo\AIComposePlugin\AIEmailService\Debug\RequestLogger;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\EmailPrompt;

final class Ollama implements CompletionProviderInterface
{
    private Curl $curl;
    private RequestLogger $requestLogger;

    public function __construct(?Curl $curl = null, ?RequestLogger $requestLogger = null)
    {
        $this->curl = $curl ?? new Curl();
        $this->requestLogger = $requestLogger ?? new RequestLogger();
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
        $maxTokens = $config['maxTokens'] ?? 750;
        if (!\is_string($model) || $model === '' || !\is_string($url) || !\in_array(parse_url($url, \PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw new ProviderException('Invalid Ollama summary configuration');
        }
        if (!\is_int($maxTokens) || $maxTokens < 1) {
            throw new ProviderException('Invalid Ollama output limit');
        }

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $prompt->getSystemInstruction()],
                ['role' => 'user', 'content' => $prompt->getUserInstruction()],
            ],
            'format' => 'json',
            'stream' => false,
            'options' => ['temperature' => 0, 'num_predict' => $maxTokens],
        ];
        $trace = $this->requestLogger->begin('Ollama', $model, $prompt, ['temperature' => 0, 'token_limit' => $maxTokens]);
        $response = null;
        $failure = null;

        try {
            $this->curl->setHeader('Content-Type', 'application/json');
            $this->curl->setOpts([\CURLOPT_TIMEOUT => 60]);
            try {
                $response = $this->curl->post($url, $payload);
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
        } catch (\Throwable $e) {
            $failure = $e;
            throw $e;
        } finally {
            $this->logResult($trace, \is_object($response) ? $response : null, $failure);
        }
    }

    /** @param null|array{id: string, started: float} $trace */
    private function logResult(?array $trace, ?object $response, ?\Throwable $failure): void
    {
        if ($trace === null) {
            return;
        }

        $details = [];
        if ($this->curl->httpStatusCode > 0) {
            $details['http_status'] = $this->curl->httpStatusCode;
        }
        if ($this->curl->error && $this->curl->errorCode) {
            $details['curl_error_code'] = (int) $this->curl->errorCode;
        }
        if ($failure !== null) {
            $details['error_type'] = \get_class($failure);
        }
        if ($response !== null && \is_string($response->done_reason ?? null)) {
            $details['finish_reason'] = $response->done_reason;
        }

        $usage = [];
        foreach (['prompt_eval_count' => 'prompt_tokens', 'eval_count' => 'completion_tokens'] as $source => $target) {
            if ($response !== null && \is_int($response->{$source} ?? null)) {
                $usage[$target] = $response->{$source};
            }
        }
        if (isset($usage['prompt_tokens'], $usage['completion_tokens'])) {
            $usage['total_tokens'] = $usage['prompt_tokens'] + $usage['completion_tokens'];
        }

        $this->requestLogger->finish($trace, $failure === null ? 'success' : 'error', $details, $usage);
    }
}
