<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Prompt;

final class EmailPrompt
{
    private string $systemInstruction;
    private string $userInstruction;
    private string $purpose;

    public function __construct(string $systemInstruction, string $userInstruction, string $purpose = 'email')
    {
        $this->systemInstruction = $systemInstruction;
        $this->userInstruction = $userInstruction;
        $this->purpose = $purpose;
    }

    public function getSystemInstruction(): string
    {
        return $this->systemInstruction;
    }

    public function getUserInstruction(): string
    {
        return $this->userInstruction;
    }

    public function getPurpose(): string
    {
        return $this->purpose;
    }
}
