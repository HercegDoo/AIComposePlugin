<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Prompt;

final class EmailPrompt
{
    private string $systemInstruction;
    private string $userInstruction;

    public function __construct(string $systemInstruction, string $userInstruction)
    {
        $this->systemInstruction = $systemInstruction;
        $this->userInstruction = $userInstruction;
    }

    public function getSystemInstruction(): string
    {
        return $this->systemInstruction;
    }

    public function getUserInstruction(): string
    {
        return $this->userInstruction;
    }
}
