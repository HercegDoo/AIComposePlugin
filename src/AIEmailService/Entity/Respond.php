<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Entity;

class Respond
{
    private string $body;
    private ?string $subject = null;
    private string $error = '';

    public function __construct(string $body)
    {
        $this->body = $body;
    }

    public function __toString(): string
    {
        return $this->body === '' ? $this->error : $this->body;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setError(string $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function getError(): string
    {
        return $this->error;
    }
}