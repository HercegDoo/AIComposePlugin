<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Providers;

use HercegDoo\AIComposePlugin\AIEmailService\Prompt\EmailPrompt;

interface CompletionProviderInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function complete(EmailPrompt $prompt, array $config): string;
}
