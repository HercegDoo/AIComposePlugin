<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Providers;

use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\Respond;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;

class DummyProvider implements InterfaceProvider
{
    public function getProviderName(): string
    {
        return 'Dummy Provider';
    }

    public function generateEmail(RequestData $requestData): Respond
    {
        return (new Respond('This is a dummy response'))->setSubject('Dummy Subject')->setBody("
            This is a dummy response to your request.
            Sender: {$requestData->getSenderName()}
            Receiver: {$requestData->getRecipientName()}
            Instructions: {$requestData->getInstruction()}
            ");
    }
}