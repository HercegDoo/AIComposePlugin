<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Entity;

use HercegDoo\AIComposePlugin\AIEmailService\Settings;

class RequestData
{
    private string $recipientName;
    private ?string $recipientEmail = null;
    private string $senderName;
    private ?string $senderEmail = null;
    private string $style;
    private string $length;
    private string $creativity;
    private string $language;
    private string $instruction;
    private ?string $fixText = null;
    private ?string $previousGeneratedEmail = null;
    private ?string $previousConversation = null;

    private function __construct(string $recipientName, string $senderName, string $instruction, ?string $style, ?string $length, ?string $creativity, ?string $language)
    {
        $this->recipientName = $recipientName;
        $this->senderName = $senderName;
        $this->style = $style ?? Settings::$STYLE_CASUAL;
        $this->length = $length ?? Settings::$LENGTH_MEDIUM;
        $this->creativity = $creativity ?? Settings::$CREATIVITY_MEDIUM;
        $this->language = $language ?? Settings::$LANGUAGE_BOSNIAN;
        $this->instruction = $instruction;
    }

    public static function createRequestData(string $recipientName, string $senderName, string $instruction, ?string $style, ?string $length, ?string $creativity, ?string $language): self
    {
        return new self($recipientName, $senderName, $instruction, $style, $length, $creativity, $language);
    }

    public function setRecipientName(string $recipientName): self
    {
        $this->recipientName = $recipientName;

        return $this;
    }

    public function setRecipientEmail(?string $recipientEmail): self
    {
        $this->recipientEmail = $recipientEmail;

        return $this;
    }

    public function setSenderName(string $senderName): self
    {
        $this->senderName = $senderName;

        return $this;
    }

    public function setSenderEmail(?string $senderEmail): self
    {
        $this->senderEmail = $senderEmail;

        return $this;
    }

    public function setStyle(string $style): self
    {
        $this->style = $style;

        return $this;
    }

    public function setLength(string $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function setCreativity(string $creativity): self
    {
        $this->creativity = $creativity;

        return $this;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function setInstruction(string $instruction): self
    {
        $this->instruction = $instruction;

        return $this;
    }

    public function setFixText(?string $previousGeneratedEmail, string $fixText): self
    {
        $this->previousGeneratedEmail = $previousGeneratedEmail;
        $this->fixText = $fixText;

        return $this;
    }

    public function setPreviousConversation(?string $previousConversation): self
    {
        $this->previousConversation = $previousConversation;

        return $this;
    }

    public function setPreviousGeneratedEmail(?string $previousGeneratedEmail): self
    {
        $this->previousGeneratedEmail = $previousGeneratedEmail;

        return $this;
    }

    public function getRecipientName(): string
    {
        return $this->recipientName;
    }

    public function getRecipientEmail(): ?string
    {
        return $this->recipientEmail;
    }

    public function getSenderName(): string
    {
        return $this->senderName;
    }

    public function getSenderEmail(): ?string
    {
        return $this->senderEmail;
    }

    public function getStyle(): string
    {
        return $this->style;
    }

    public function getLength(): string
    {
        return $this->length;
    }

    public function getCreativity(): string
    {
        return $this->creativity;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getInstruction(): string
    {
        return $this->instruction;
    }

    public function getFixText(): ?string
    {
        return $this->fixText;
    }

    public function getPreviousGeneratedEmail(): ?string
    {
        return $this->previousGeneratedEmail;
    }

    public function getPreviousConversation(): ?string
    {
        return $this->previousConversation;
    }
}
