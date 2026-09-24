<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Providers;

use Curl\Curl;
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
    public function __construct($curl = null)
    {
        $this->curl = $curl ?: new Curl();
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
        $respond = $this->sendRequest($prompt);

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
    }

    private function sendRequest(EmailPrompt $prompt): \stdClass
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
            $respond = $curl->post($this->apiUrl, $this->buildPayload($prompt));
        } catch (\Throwable $e) {
            throw new ProviderException('APIThrowable: ' . $e->getMessage());
        }

        if ($curl->error) {
            throw new ProviderException('APICurl: ' . $curl->errorMessage);
        }

        return (object) $respond;
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
