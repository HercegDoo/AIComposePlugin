<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Providers;

use Curl\Curl;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\EmailPrompt;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;

final class OpenAI extends AbstractProvider
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
        $providerConfig = Settings::getProviderConfig();
        $this->apiKey = $providerConfig['apiKey'];
        $this->apiUrl = !empty($providerConfig['apiUrl'])
            ? $providerConfig['apiUrl']
            : self::DEFAULT_API_URL;
        $this->model = $providerConfig['model'];
        $this->maxTokens = Settings::getDefaultMaxTokens();

        $this->creativity = $this->creativityMap[Settings::getCreativity()];
        $respond = $this->sendRequest($prompt);

        if ($this->hasErrors()) {
            throw new ProviderException(implode(', ', $this->getErrors()));
        }

        $email = $respond->choices[0]->message->content ?? '';
        if ($email === '') {
            throw new ProviderException('No email content found');
        }

        return new Respond($email);
    }

    private function sendRequest(EmailPrompt $prompt): \stdClass
    {
        $curl = $this->curl;

        $curl->setHeader('Content-Type', 'application/json');
        $curl->setHeader('Authorization', 'Bearer ' . $this->apiKey);

        $curl->setOpts([
            \CURLOPT_TIMEOUT => 60,
            // not verifying the ssl certificate
            \CURLOPT_SSL_VERIFYPEER => false,
            \CURLOPT_SSL_VERIFYHOST => false,
        ]);

        try {
            $respond = $curl->post($this->apiUrl, [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $prompt->getSystemInstruction()],
                    ['role' => 'user', 'content' => $prompt->getUserInstruction()],
                ],
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->creativity,
                'n' => 1,
                'stream' => false,
            ]);
        } catch (\Throwable $e) {
            throw new ProviderException('APIThrowable: ' . $e->getMessage());
        }

        if ($curl->error) {
            throw new ProviderException('APICurl: ' . $curl->errorMessage);
        }

        return (object) $respond;
    }
}
