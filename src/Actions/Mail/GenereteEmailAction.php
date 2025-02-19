<?php

namespace HercegDoo\AIComposePlugin\Actions\Mail;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;
use HercegDoo\AIComposePlugin\Actions\ValidateAction;
use HercegDoo\AIComposePlugin\AIEmailService\AIEmail;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Request;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;

final class GenereteEmailAction extends AbstractAction implements ValidateAction
{
    private ?string $senderName;
    private ?string $recipientName;
    private ?string $length;

    private ?string $style;
    private ?string $creativity;
    private ?string $language;
    private ?string $recipientEmail;
    private ?string $subject;
    private ?string $instructions;
    private ?string $senderEmail;

    private ?string $previousConversation;

    private ?string $fixText;

    private ?string $previousGeneratedEmailText;
    private ?string $signaturePresent;

    private ?string $multipleRecipients;

    private RequestData $aiRequestData;

    public function handler(): void
    {
        header('Content-Type: application/json');
        try {
            $status = 'success';
            $this->preparePostData();
            $email = AIEmail::generate($this->aiRequestData);
            $respond = $email->getBody();

            if ($this->hasErrors()) {
                $status = 'error';
            }

            echo json_encode([
                'status' => $status,
                'respond' => $respond,
            ]);
        } catch (\Throwable $e) {
            error_log('Error message: ' . $e->getMessage());
            error_log('Error code: ' . $e->getCode());
            error_log('Error file: ' . $e->getFile());
            error_log('Error line: ' . $e->getLine());

            $this->rcmail->output->show_message($this->translation('ai_request_error'), 'error');
            $this->rcmail->output->send();
        }
    }

    public function validate(): void
    {
        $languages = array_values(Settings::getLanguages());
        $lengths = array_values(Settings::getLengths());
        $creativities = array_values(Settings::getCreativities());
        $styles = array_values(Settings::getStyles());

        $this->senderName = Request::postString('senderName');
        $this->recipientName = Request::postString('recipientName');
        $this->style = Request::postString('style');
        $this->length = Request::postString('length');
        $this->creativity = Request::postString('creativity');
        $this->language = Request::postString('language');
        $this->recipientEmail = Request::postString('recipientEmail');
        $this->subject = Request::postString('subject');
        $this->instructions = Request::postString('instructions');
        $this->senderEmail = Request::postString('senderEmail');
        $this->previousConversation = Request::postString('previousConversation');
        $this->fixText = Request::postString('fixText');
        $this->previousGeneratedEmailText = Request::postString('previousGeneratedEmailText');
        $this->signaturePresent = Request::postString('signaturePresent');
        $this->multipleRecipients = Request::postString('multipleRecipients');
        $this->nameValidation($this->senderName);
        $this->nameValidation($this->recipientName, true);
        $this->selectValidation($this->style, $styles, 'style');
        $this->selectValidation($this->length, $lengths, 'length');
        $this->selectValidation($this->creativity, $creativities, 'creativity');
        $this->selectValidation($this->language, $languages, 'language');
        $this->emailValidation($this->recipientEmail, 'recipient', true);
        $this->emailValidation($this->senderEmail, 'sender');
        $this->subjectValidation($this->subject);
        $this->instructionsValidation($this->instructions);
    }

    private function preparePostData(): void
    {
        $this->aiRequestData = RequestData::make((string) $this->recipientName, (string) $this->senderName, (string) $this->instructions, $this->style, $this->length, $this->creativity, $this->language);

        $this->aiRequestData->setRecipientEmail($this->recipientEmail);
        $this->aiRequestData->setSenderEmail($this->senderEmail);
        $this->aiRequestData->setPreviousConversation($this->previousConversation);
        $this->aiRequestData->setSubject((string) $this->subject);
        $this->aiRequestData->setFixText($this->previousGeneratedEmailText, (string) $this->fixText);
        $this->aiRequestData->setSignaturePresent((bool) $this->signaturePresent);
        $this->aiRequestData->setMultipleRecipients((bool) $this->multipleRecipients);
    }

    private function hasNoLetters(string $string): bool
    {
        return !preg_match('/[a-zA-Z]/', $string);
    }

    private function nameValidation(?string $name, bool $isRecipientName = false, bool $hasEntered = false): void
    {
        if ($isRecipientName && !$hasEntered) {
            $recipientNamesArray = array_filter(array_map('trim', explode(',', (string) $name)));
            foreach ($recipientNamesArray as $recipientName) {
                $this->nameValidation($recipientName, true, true);
            }
        }

        if (empty($name) && $isRecipientName) {
            $this->recipientName = '';
        }

        if (empty($name) && !$isRecipientName) {
            $this->setError($this->translation('ai_validation_error_sender_name_mandatory'));
        }

        if ($this->hasNoLetters((string) $name) && \strlen((string) $name) > 1) {
            $this->setError($this->translation('ai_validation_error_invalid_' . ($isRecipientName ? 'recipient' : 'sender') . '_name_text'));
        }

        if (!empty($name) && \strlen($name) < 3) {
            $this->setError($this->translation('ai_validation_error_not_enough_characters_' . ($isRecipientName ? 'recipient' : 'sender') . '_name'));
        }
    }

    /**
     * @param string[] $originalOptionsArray
     */
    private function selectValidation(?string $selectValue, array $originalOptionsArray, string $field): void
    {
        $selectValue = $field === 'language' ? ucfirst((string) $selectValue) : $selectValue;
        if (!\in_array($selectValue, $originalOptionsArray, true)) {
            $this->setError($this->translation('ai_validation_error_invalid_' . $field));
        }
    }

    private function emailValidation(?string $email, string $emailParticipant, bool $isRecipientEmail = false): void
    {
        $validateEmail = function (string $email) use ($emailParticipant): void {
            if (!\rcube_utils::check_email($email)) {
                $this->setError($this->translation('ai_validation_error_invalid_' . $emailParticipant . '_email_address'));
            }
        };

        if ($isRecipientEmail) {
            $recipientEmailsArray = array_filter(array_map('trim', explode(',', (string) $email)));

            foreach ($recipientEmailsArray as $recipientEmail) {
                $validateEmail($recipientEmail);
            }
        } elseif (!empty($email)) {
            $validateEmail($email);
        }
    }

    private function subjectValidation(?string $subject): void
    {
        if (!empty($subject) && $this->hasNoLetters($subject)) {
            $this->setError($this->translation('ai_validation_error_invalid_input_subject'));
        }
    }

    private function instructionsValidation(?string $instructions): void
    {
        if (empty($instructions)) {
            $this->setError($this->translation('ai_validation_error_not_enough_characters_instruction'));
        }
    }
}
