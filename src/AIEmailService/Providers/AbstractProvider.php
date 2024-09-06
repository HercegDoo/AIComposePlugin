<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Providers;

abstract class AbstractProvider implements InterfaceProvider
{
    /**
     *
     * @var string[]
     */
    private array $errors = [];

    public function setError(string $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }
}
