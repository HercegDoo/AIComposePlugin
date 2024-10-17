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
    private string $apiKey;
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
        $this->apiKey = Settings::getProviderConfig()['apiKey'];
        $this->model = Settings::getProviderConfig()['model'];
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
        if ($requestData->getFixText()) {
            $prompt = " Write the same email as this {$requestData->getPreviousGeneratedEmail()}, in the same language, but change this text snippet from that same email: {$requestData->getFixText()} based on this instruction {$requestData->getInstruction()}." .
                ($requestData->getPreviousConversation() ? " Previous conversation: {$requestData->getPreviousConversation()}." : '');
        } else {
            $prompt = "Create a {$requestData->getStyle()} email with the following specifications:" .
                (!empty($requestData->getSubject()) ? " Subject: {$requestData->getSubject()}" : ' Without a subject') .
                ($requestData->getRecipientName() !== '' ? " *Recipient: {$requestData->getRecipientName()}" : '') .
                " *Sender: {$requestData->getSenderName()}" .
                " *Language: {$requestData->getLanguage()}" .
                " *Length: {$requestData->getLength()}" .
                " *The email is about: {$requestData->getInstruction()}." .
                'Do not write the subject if provided, it is only there for your context' .
                ($requestData->getPreviousConversation() ? " Previous conversation: {$requestData->getPreviousConversation()}." : '') .
                ($requestData->getSignaturePresent() ? 'Do not sign the email with any name, do not write anything after the last greeting, no names at the end of the email' : '');
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
            // not verifying the ssl certificate
            \CURLOPT_SSL_VERIFYPEER => false,
            \CURLOPT_SSL_VERIFYHOST => false,
        ]);

        try {
            $respond = $curl->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful personal assistant.'],
                    ['role' => 'user', 'content' => $prompt],
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
