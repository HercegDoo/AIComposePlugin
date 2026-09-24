<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\AIEmailService\Prompt;

use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;

final class EmailPromptBuilder implements PromptBuilderInterface
{
    private const SYSTEM_INSTRUCTION = 'You are a helpful personal assistant.';

    public function build(RequestData $requestData): EmailPrompt
    {
        $instruction = $requestData->getFixText()
            ? $this->buildRevisionInstruction($requestData)
            : $this->buildNewEmailInstruction($requestData);

        return new EmailPrompt(self::SYSTEM_INSTRUCTION, $instruction);
    }

    private function buildRevisionInstruction(RequestData $requestData): string
    {
        return " Write an identical email as this {$requestData->getPreviousGeneratedEmail()}, in the same language, but change only this text snippet from that same email: {$requestData->getFixText()} based on this instruction {$requestData->getInstruction()}." .
            $this->previousConversationInstruction($requestData) .
            $this->existingSignatureInstruction($requestData);
    }

    private function buildNewEmailInstruction(RequestData $requestData): string
    {
        $addressMultiplePeople = $requestData->getMultipleRecipients() ? ' Address the recipient in plural form.' : '';

        return "Create a {$requestData->getStyle()} email with the following specifications:" .
            (!empty($requestData->getSubject()) ? " Subject: {$requestData->getSubject()}" : ' Without a subject') .
            ($requestData->getRecipientName() !== '' ? " *Recipient: {$requestData->getRecipientName()}" : '') .
            " *Sender: {$requestData->getSenderName()}" .
            " *Language: {$requestData->getLanguage()}" .
            " *Length: {$requestData->getLength()}." .
            $addressMultiplePeople .
            " Compose a well-structured email based on this instruction: {$requestData->getInstruction()}. The instruction should be rewritten in the tone and format of a {$requestData->getStyle()} email to a reader. " .
            " If the instruction contains pronouns (like 'he', 'she', 'they', etc.), assume they refer to the recipient unless specified otherwise." .
            " The number of words should be {$requestData->getLengthWords($requestData->getLength())}. " .
            'Do not write the subject if provided, it is only there for your context. ' .
            'Only greet the recipient, never the sender. ' .
            'The format should be as follows:' . "\n" .
            'Greeting' . "\n\n" .
            'Content' . "\n\n" .
            ($requestData->getSignaturePresent() ? '' : 'Closing Greeting' . "\n") .
            $this->previousConversationInstruction($requestData) .
            $this->existingSignatureInstruction($requestData);
    }

    private function existingSignatureInstruction(RequestData $requestData): string
    {
        return $requestData->getSignaturePresent()
            ? ' End the email after its message body; do not add a closing greeting, signature, sender name, or contact details.'
            : '';
    }

    private function previousConversationInstruction(RequestData $requestData): string
    {
        return $requestData->getPreviousConversation()
            ? " Previous conversation: {$requestData->getPreviousConversation()}."
            : '';
    }
}
