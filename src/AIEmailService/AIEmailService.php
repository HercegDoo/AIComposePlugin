<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService;

use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;
use HercegDoo\AIComposePlugin\AIEmailService\Providers\InterfaceProvider;

class AIEmailService
{
    private InterfaceProvider $provider;
    private Settings $settings;

    private function __construct(Settings $settings)
    {
        $this->settings = $settings;
        $this->provider = $settings->getProvider();
    }

    public static function createAIEmailService(Settings $settings): self
    {
        return new self( $settings);
    }

    /**
     * @throws ProviderException
     */
    public function generateEmail(RequestData $requestData): Respond
    {
        try {
            $respond = $this->provider->generateEmail($requestData, $this->settings);
        } catch (\Throwable $e) {
            if ($e instanceof ProviderException) {
                throw $e;
            }

            throw new ProviderException('General: ' . $e->getMessage());
        }

        return $respond;
    }
}
