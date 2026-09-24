<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Prompt;

use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;

interface PromptBuilderInterface
{
    public function build(RequestData $requestData): EmailPrompt;
}
