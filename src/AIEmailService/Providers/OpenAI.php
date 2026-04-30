<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Providers;

use Curl\Curl;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
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
    public function generateEmail(RequestData $requestData): Respond
    {
        $providerConfig = Settings::getProviderConfig();
        $this->apiKey = $providerConfig['apiKey'];
        $this->apiUrl = !empty($providerConfig['apiUrl'])
            ? $providerConfig['apiUrl']
            : self::DEFAULT_API_URL;
        $this->model = $providerConfig['model'];
        $this->maxTokens = Settings::getDefaultMaxTokens();

        $this->creativity = $this->creativityMap[Settings::getCreativity()];
        $prompt = $this->prompt($requestData);

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

    private function prompt(RequestData $requestData): string
    {
        $adressMultiplePeople = $requestData->getMultipleRecipients() ? ' Address the recipient in plural form.' : '';

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
                ($requestData->getSignaturePresent() ? 'CRUCIAL: "Write an email without signing it or including any identifying information after the greeting, including no names or titles. Only include the message and greeting, but leave the signature and closing blank."' : '');
        }

        return $prompt;
    }

    private function sendRequest(string $prompt): \stdClass
    {
        $curl = $this->curl;

        $curl->setHeader('Content-Type', 'application/json');
        $curl->setHeader('Authorization', 'Bearer ' . $this->apiKey);

        $curl->setOpts([
            \CURLOPT_TIMEOUT => 60,
            // Habilitar verificação SSL para segurança
            \CURLOPT_SSL_VERIFYPEER => true,
            \CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        try {
            $respond = $curl->post($this->apiUrl, [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful personal assistant.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_completion_tokens' => $this->maxTokens,
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
