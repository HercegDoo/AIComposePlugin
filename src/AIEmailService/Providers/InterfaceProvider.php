<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Providers;

use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
use HercegDoo\AIComposePlugin\AIEmailService\Exceptions\ProviderException;

interface InterfaceProvider
{
    public function getProviderName(): string;

    /**
     * @throws ProviderException
     */
    public function generateEmail(RequestData $requestData): Respond;
}
