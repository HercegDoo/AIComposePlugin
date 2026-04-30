<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Providers;

use Curl\Curl;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;

final class Gemini extends AbstractProvider
{
    /**
     * Base URL for the Gemini API.
     * The model name is appended at request time.
     * Format: https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent
     */
    private const DEFAULT_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/';

    private string $apiKey;
    private string $apiUrl;
    private Curl $curl;
    private float $creativity;
    private string $model;
    private int $maxTokens;

    /**
     * Maps human-readable creativity levels to Gemini temperature values (0.0 – 1.0).
     *
     * @var array<string, float>
     */
    private array $creativityMap = [
        'low'    => 0.2,
        'medium' => 0.5,
        'high'   => 0.8,
    ];

    /**
     * @param Curl|null $curl
     */
    public function __construct($curl = null)
    {
        $this->curl = $curl ?: new Curl();
    }

    public function getProviderName(): string
    {
        return 'Gemini';
    }

    /**
     * @throws ProviderException
     */
    public function generateEmail(RequestData $requestData): Respond
    {
        $providerConfig   = Settings::getProviderConfig();
        $this->apiKey     = $providerConfig['apiKey'];
        $this->model      = $providerConfig['model'] ?? 'gemini-2.5-flash';
        $this->maxTokens  = Settings::getDefaultMaxTokens();
        $this->creativity = $this->creativityMap[Settings::getCreativity()] ?? 0.5;

        $this->apiUrl = !empty($providerConfig['apiUrl'])
            ? $providerConfig['apiUrl']
            : self::DEFAULT_API_URL . $this->model . ':generateContent';

        $prompt  = $this->prompt($requestData);
        $respond = $this->sendRequest($prompt);

        if ($this->hasErrors()) {
            throw new ProviderException(implode(', ', $this->getErrors()));
        }

        // Gemini response path: candidates[0].content.parts[0].text
        $email = $respond->candidates[0]->content->parts[0]->text ?? '';
        if ($email === '') {
            throw new ProviderException('No email content found in Gemini response');
        }

        return new Respond($email);
    }

    /**
     * Builds the prompt string identical to the OpenAI provider logic.
     */
    private function prompt(RequestData $requestData): string
    {
        $adressMultiplePeople = $requestData->getMultipleRecipients()
            ? ' Address the recipient in plural form.'
            : '';

        if ($requestData->getFixText()) {
            $prompt = " Write an identical email as this {$requestData->getPreviousGeneratedEmail()}, in the same language, but change only this text snippet from that same email: {$requestData->getFixText()} based on this instruction {$requestData->getInstruction()}." .
                ($requestData->getPreviousConversation() ? " Previous conversation: {$requestData->getPreviousConversation()}." : '');
        } else {
            $prompt = "Create a {$requestData->getStyle()} email with the following specifications:" .
                (!empty($requestData->getSubject()) ? " Subject: {$requestData->getSubject()}" : ' Without a subject') .
                ($requestData->getRecipientName() !== '' ? " *Recipient: {$requestData->getRecipientName()}" : '') .
                " *Sender: {$requestData->getSenderName()}" .
                " *Language: {$requestData->getLanguage()}" .
                " *Length: {$requestData->getLength()}." .
                $adressMultiplePeople .
                " Compose a well-structured email based on this instruction: {$requestData->getInstruction()}. The instruction should be rewritten in the tone and format of a {$requestData->getStyle()} email to a reader. " .
                " If the instruction contains pronouns (like 'he', 'she', 'they', etc.), assume they refer to the recipient unless specified otherwise." .
                " The number of words should be {$requestData->getLengthWords($requestData->getLength())}. " .
                'Do not write the subject if provided, it is only there for your context. ' .
                'Only greet the recipient, never the sender. ' .
                'The format should be as follows:' . "\n" .
                'Greeting' . "\n\n" .
                'Content' . "\n\n" .
                'Closing Greeting' . "\n" .
                ($requestData->getPreviousConversation() ? " Previous conversation: {$requestData->getPreviousConversation()}." : '') .
                ($requestData->getSignaturePresent()
                    ? 'CRUCIAL: "Write an email without signing it or including any identifying information after the greeting, including no names or titles. Only include the message and greeting, but leave the signature and closing blank."'
                    : '');
        }

        return $prompt;
    }

    /**
     * Sends the prompt to the Gemini generateContent endpoint using raw cURL
     * to guarantee the body is sent as JSON.
     *
     * NOTE: php-curl-class encodes PHP arrays as multipart/form-data when passed
     * directly to post(), which breaks the Gemini API. We bypass the wrapper and
     * use curl_exec() directly with a pre-encoded JSON string.
     *
     * @throws ProviderException
     */
    private function sendRequest(string $prompt): \stdClass
    {
        $jsonBody = json_encode([
            'system_instruction' => [
                'parts' => [
                    ['text' => 'You are a helpful personal assistant.'],
                ],
            ],
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature'     => $this->creativity,
                'maxOutputTokens' => $this->maxTokens,
                'candidateCount'  => 1,
            ],
        ]);

        if ($jsonBody === false) {
            throw new ProviderException('Failed to encode request body as JSON');
        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            \CURLOPT_URL            => $this->apiUrl,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_POST           => true,
            \CURLOPT_POSTFIELDS     => $jsonBody,
            \CURLOPT_TIMEOUT        => 60,
            \CURLOPT_SSL_VERIFYPEER => false,
            \CURLOPT_SSL_VERIFYHOST => false,
            \CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Content-Length: ' . \strlen($jsonBody),
                'x-goog-api-key: ' . $this->apiKey,
            ],
        ]);

        $raw   = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            throw new ProviderException('APICurl: ' . $error);
        }

        if ($raw === false || $raw === '') {
            throw new ProviderException('APICurl: empty response from Gemini API');
        }

        $result = json_decode((string) $raw);

        if (!\is_object($result)) {
            throw new ProviderException('APICurl: invalid JSON response from Gemini API');
        }

        // Surface API-level errors returned by Gemini (invalid key, quota exceeded, etc.)
        if (isset($result->error)) {
            throw new ProviderException(
                'GeminiAPI [HTTP ' . $httpCode . ']: ' . ($result->error->message ?? 'Unknown API error')
            );
        }

        return $result;
    }
}
