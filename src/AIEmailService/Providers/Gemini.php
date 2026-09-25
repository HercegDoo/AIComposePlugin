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

final class Gemini extends AbstractProvider implements CompletionProviderInterface
{
    private const API_ROOT = 'https://generativelanguage.googleapis.com/v1beta/models/';
    private const CREATIVITY_TEMPERATURE = ['low' => 0.2, 'medium' => 0.5, 'high' => 0.8];

    private Curl $curl;
    private RequestLogger $requestLogger;

    public function __construct(?Curl $curl = null, ?RequestLogger $requestLogger = null)
    {
        $this->curl = $curl ?? new Curl();
        $this->requestLogger = $requestLogger ?? new RequestLogger();
    }

    public function getProviderName(): string
    {
        return 'Gemini';
    }

    public function generateEmail(RequestData $requestData, EmailPrompt $prompt): Respond
    {
        $config = Settings::getProviderConfig();
        if (!isset($config['temperature'])) {
            $config['temperature'] = self::CREATIVITY_TEMPERATURE[$requestData->getCreativity()] ?? 0.5;
        }

        return new Respond($this->complete($prompt, $config));
    }

    /**
     * @param array<string, mixed> $config
     *
     * @throws ProviderException
     */
    public function complete(EmailPrompt $prompt, array $config): string
    {
        $apiKey = $config['apiKey'] ?? null;
        $model = $config['model'] ?? null;
        $maxTokens = $config['maxTokens'] ?? Settings::getDefaultMaxTokens();
        if (!\is_string($apiKey) || trim($apiKey) === ''
            || !\is_string($model) || !preg_match('/^gemini-[a-zA-Z0-9][a-zA-Z0-9._-]*$/D', $model)
            || !\is_int($maxTokens) || $maxTokens < 1) {
            throw new ProviderException('Invalid Gemini configuration');
        }

        $generationConfig = ['maxOutputTokens' => $maxTokens];
        $thinkingLevel = $config['thinkingLevel'] ?? 'low';
        if (!\in_array($thinkingLevel, ['low', 'medium', 'high'], true)) {
            throw new ProviderException('Invalid Gemini thinking level');
        }
        if (preg_match('/^gemini-3(?:[.-]|$)/', $model)) {
            $generationConfig['thinkingConfig'] = ['thinkingLevel' => $thinkingLevel];
        } else {
            $temperature = $config['temperature'] ?? self::CREATIVITY_TEMPERATURE[Settings::getCreativity()];
            if ((!\is_int($temperature) && !\is_float($temperature)) || $temperature < 0 || $temperature > 2) {
                throw new ProviderException('Invalid Gemini temperature');
            }
            $generationConfig['temperature'] = (float) $temperature;
        }

        $payload = [
            'systemInstruction' => ['parts' => [['text' => $prompt->getSystemInstruction()]]],
            'contents' => [['role' => 'user', 'parts' => [['text' => $prompt->getUserInstruction()]]]],
            'generationConfig' => $generationConfig,
        ];
        /** @var array<string, float|int|string> $logOptions */
        $logOptions = ['token_limit' => $maxTokens];
        if (isset($generationConfig['thinkingConfig'])) {
            $logOptions['thinking_level'] = $thinkingLevel;
        } elseif (\is_float($generationConfig['temperature'] ?? null)) {
            $logOptions['temperature'] = $generationConfig['temperature'];
        }
        $trace = $this->requestLogger->begin('Gemini', $model, $prompt, $logOptions);
        $response = null;
        $failure = null;

        try {
            $this->curl->setHeader('Content-Type', 'application/json');
            $this->curl->setHeader('x-goog-api-key', $apiKey);
            $this->curl->setOpts([
                \CURLOPT_TIMEOUT => 60,
                \CURLOPT_SSL_VERIFYPEER => true,
                \CURLOPT_SSL_VERIFYHOST => 2,
            ]);

            try {
                $response = $this->curl->post(self::API_ROOT . $model . ':generateContent', $payload);
            } catch (\Throwable $e) {
                throw new ProviderException('Gemini request failed');
            }

            if ($this->curl->error) {
                throw new ProviderException('Gemini API request failed');
            }
            if (!\is_object($response)) {
                throw new ProviderException('Invalid Gemini response');
            }

            $parts = $response->candidates[0]->content->parts ?? null;
            $text = '';
            if (\is_array($parts)) {
                foreach ($parts as $part) {
                    if (\is_object($part) && ($part->thought ?? false) !== true && \is_string($part->text ?? null)) {
                        $text .= $part->text;
                    }
                }
            }

            $finishReason = $response->candidates[0]->finishReason ?? null;
            if (trim($text) === '') {
                if ($finishReason === 'MAX_TOKENS') {
                    throw new ProviderException('No Gemini content found: the model reached the output token limit. Increase aiDefaultMaxTokens.');
                }
                if (\is_string($response->promptFeedback->blockReason ?? null)) {
                    throw new ProviderException('Gemini prompt blocked');
                }

                throw new ProviderException('No Gemini content found');
            }
            if (\is_string($finishReason) && !\in_array($finishReason, ['STOP', 'MAX_TOKENS'], true)) {
                throw new ProviderException('Gemini response blocked or incomplete');
            }

            return $text;
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
        $responseId = $response->responseId ?? null;
        if (\is_string($responseId)) {
            $details['provider_request_id'] = $responseId;
        }
        $finishReason = $response->candidates[0]->finishReason ?? null;
        if (\is_string($finishReason)) {
            $details['finish_reason'] = $finishReason;
        }
        $blockReason = $response->promptFeedback->blockReason ?? null;
        if (\is_string($blockReason)) {
            $details['block_reason'] = $blockReason;
        }

        $usage = [];
        $reportedUsage = $response->usageMetadata ?? null;
        if (\is_object($reportedUsage)) {
            foreach ([
                'promptTokenCount' => 'prompt_tokens',
                'candidatesTokenCount' => 'completion_tokens',
                'totalTokenCount' => 'total_tokens',
                'thoughtsTokenCount' => 'reasoning_tokens',
                'cachedContentTokenCount' => 'cached_tokens',
            ] as $source => $target) {
                if (\is_int($reportedUsage->{$source} ?? null)) {
                    $usage[$target] = $reportedUsage->{$source};
                }
            }
        }

        $this->requestLogger->finish($trace, $failure === null ? 'success' : 'error', $details, $usage);
    }
}
