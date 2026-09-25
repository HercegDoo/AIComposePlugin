<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Providers;

use Curl\Curl;
use HercegDoo\AIComposePlugin\AIEmailService\Debug\RequestLogger;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\EmailPrompt;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;

final class OpenAI extends AbstractProvider implements CompletionProviderInterface
{
    private const DEFAULT_API_URL = 'https://api.openai.com/v1/chat/completions';

    private string $apiKey;
    private string $apiUrl;
    private Curl $curl;
    private float $creativity;
    private string $model;
    private int $maxTokens;
    private RequestLogger $requestLogger;

    /**
     * @var array<int|string, float>
     */
    private array $creativityMap = [
        'low' => 0.2,
        'medium' => 0.5,
        'high' => 0.8,
    ];

    /**
     * @param Curl $curl
     */
    public function __construct($curl = null, ?RequestLogger $requestLogger = null)
    {
        $this->curl = $curl ?: new Curl();
        $this->requestLogger = $requestLogger ?? new RequestLogger();
    }

    public function getProviderName(): string
    {
        return 'OpenAI';
    }

    /**
     * @throws ProviderException
     */
    public function generateEmail(RequestData $requestData, EmailPrompt $prompt): Respond
    {
        return new Respond($this->complete($prompt, Settings::getProviderConfig()));
    }

    /**
     * @param array<string, mixed> $providerConfig
     *
     * @throws ProviderException
     */
    public function complete(EmailPrompt $prompt, array $providerConfig): string
    {
        $apiKey = $providerConfig['apiKey'] ?? null;
        $apiUrl = $providerConfig['apiUrl'] ?? self::DEFAULT_API_URL;
        $model = $providerConfig['model'] ?? null;
        $maxTokens = $providerConfig['maxTokens'] ?? Settings::getDefaultMaxTokens();
        if (!\is_string($apiKey) || $apiKey === '' || !\is_string($apiUrl) || $apiUrl === ''
            || !\is_string($model) || $model === '' || !\is_int($maxTokens) || $maxTokens < 1) {
            throw new ProviderException('Invalid OpenAI configuration');
        }
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->model = $model;
        $this->maxTokens = $maxTokens;

        $temperature = $providerConfig['temperature'] ?? $this->creativityMap[Settings::getCreativity()];
        if (!\is_int($temperature) && !\is_float($temperature)) {
            throw new ProviderException('Invalid OpenAI temperature');
        }
        $this->creativity = (float) $temperature;
        $payload = $this->buildPayload($prompt);
        /** @var array<string, float|int|string> $options */
        $options = ['token_limit' => $this->maxTokens];
        if (isset($payload['temperature'])) {
            $options['temperature'] = $this->creativity;
        }
        if (\is_string($payload['reasoning_effort'] ?? null)) {
            $options['reasoning_effort'] = $payload['reasoning_effort'];
        }
        $trace = $this->requestLogger->begin('OpenAI', $this->model, $prompt, $options);
        $respond = null;
        $failure = null;

        try {
            $respond = $this->sendRequest($payload);

            if ($this->hasErrors()) {
                throw new ProviderException(implode(', ', $this->getErrors()));
            }

            $email = $respond->choices[0]->message->content ?? '';
            if ($email === '') {
                if (($respond->choices[0]->finish_reason ?? null) === 'length') {
                    throw new ProviderException('No email content found: the model reached the output token limit. Increase aiDefaultMaxTokens.');
                }

                throw new ProviderException('No email content found');
            }

            return $email;
        } catch (\Throwable $e) {
            $failure = $e;
            throw $e;
        } finally {
            $this->logResult($trace, $respond, $failure);
        }
    }

    /** @param array<string, mixed> $payload */
    private function sendRequest(array $payload): \stdClass
    {
        $curl = $this->curl;

        $curl->setHeader('Content-Type', 'application/json');
        $curl->setHeader('Authorization', 'Bearer ' . $this->apiKey);

        $curl->setOpts([
            \CURLOPT_TIMEOUT => 60,
            \CURLOPT_SSL_VERIFYPEER => true,
            \CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        try {
            $respond = $curl->post($this->apiUrl, $payload);
        } catch (\Throwable $e) {
            throw new ProviderException('APIThrowable: ' . $e->getMessage());
        }

        if ($curl->error) {
            throw new ProviderException('APICurl: ' . $curl->errorMessage);
        }

        return (object) $respond;
    }

    /**
     * @param null|array{id: string, started: float} $trace
     */
    private function logResult(?array $trace, ?\stdClass $response, ?\Throwable $failure): void
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
        $providerError = $this->curl->response->error ?? null;
        if (\is_object($providerError)) {
            foreach (['type', 'code'] as $field) {
                $value = $providerError->{$field} ?? null;
                if (\is_string($value) && preg_match('/^[a-zA-Z0-9_.-]{1,80}$/', $value)) {
                    $details['provider_error_' . $field] = $value;
                }
            }
        }
        if ($response !== null) {
            if (isset($response->id) && \is_string($response->id)) {
                $details['provider_request_id'] = $response->id;
            }
            $finishReason = $response->choices[0]->finish_reason ?? null;
            if (\is_string($finishReason)) {
                $details['finish_reason'] = $finishReason;
            }
        }

        $usage = [];
        $reportedUsage = $response->usage ?? null;
        if (\is_object($reportedUsage)) {
            foreach (['prompt_tokens', 'completion_tokens', 'total_tokens'] as $name) {
                if (isset($reportedUsage->{$name}) && \is_int($reportedUsage->{$name})) {
                    $usage[$name] = $reportedUsage->{$name};
                }
            }
            $cached = $reportedUsage->prompt_tokens_details->cached_tokens ?? null;
            if (\is_int($cached)) {
                $usage['cached_tokens'] = $cached;
            }
            $reasoning = $reportedUsage->completion_tokens_details->reasoning_tokens ?? null;
            if (\is_int($reasoning)) {
                $usage['reasoning_tokens'] = $reasoning;
            }
        }

        $this->requestLogger->finish($trace, $failure === null ? 'success' : 'error', $details, $usage);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPayload(EmailPrompt $prompt): array
    {
        $isModernModel = preg_match('/^gpt-[56](?:[.-]|$)/', $this->model) === 1;
        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => $isModernModel ? 'developer' : 'system', 'content' => $prompt->getSystemInstruction()],
                ['role' => 'user', 'content' => $prompt->getUserInstruction()],
            ],
            'n' => 1,
            'stream' => false,
        ];

        // GPT-5 and GPT-6 use the completion token limit, which also includes reasoning tokens.
        if ($isModernModel) {
            $payload['max_completion_tokens'] = $this->maxTokens;

            // Reduced reasoning effort leaves room for the visible email within the token limit.
            if (preg_match('/^gpt-6-(?:astra|sol|luna)(?:-\d{4}-\d{2}-\d{2})?$/', $this->model)) {
                $payload['reasoning_effort'] = 'low';
            } elseif (preg_match('/^gpt-5(?:-mini|-nano)?(?:-\d{4}-\d{2}-\d{2})?$/', $this->model)) {
                $payload['reasoning_effort'] = 'minimal';
            }

            return $payload;
        }

        $payload['max_tokens'] = $this->maxTokens;
        $payload['temperature'] = $this->creativity;

        return $payload;
    }
}
