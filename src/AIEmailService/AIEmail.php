<?php

namespace HercegDoo\AIComposePlugin\AIEmailService;

use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\EmailPromptBuilder;
use HercegDoo\AIComposePlugin\AIEmailService\Prompt\PromptBuilderInterface;

final class AIEmail
{
    /**
     * @throws ProviderException
     */
    public static function generate(RequestData $requestData, ?PromptBuilderInterface $promptBuilder = null): Respond
    {
        $provider = Settings::getProvider();

        try {
            $prompt = ($promptBuilder ?? new EmailPromptBuilder())->build($requestData);
            $respond = $provider->generateEmail($requestData, $prompt);
        } catch (\Throwable $e) {
            if ($e instanceof ProviderException) {
                throw $e;
            }

            throw new ProviderException('General: ' . $e->getMessage());
        }

        return $respond;
    }
}
