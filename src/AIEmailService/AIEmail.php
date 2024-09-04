<?php

namespace HercegDoo\AIComposePlugin\AIEmailService;

use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;

final class AIEmail
{
    /**
     * @throws ProviderException
     */
    public static function generate(RequestData $requestData): Respond
    {
        $provider = Settings::getProvider();

        try {
            $respond = $provider->generateEmail($requestData);
        } catch (\Throwable $e) {
            if ($e instanceof ProviderException) {
                throw $e;
            }

            throw new ProviderException('General: ' . $e->getMessage());
        }

        return $respond;
    }
}
