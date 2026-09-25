<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Actions\Mail;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;
use HercegDoo\AIComposePlugin\AIEmailService\AIEmail;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Request;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;

final class GenerateSubjectAction extends AbstractAction
{
    private string $draft = '';
    private string $language = '';
    private string $previousSubject = '';

    public function validate(): void
    {
        $body = Request::postString('body') ?? '';
        $instructions = Request::postString('instructions') ?? '';
        $this->draft = trim($body) !== '' ? $body : $instructions;
        $language = Request::postString('language') ?? Settings::getDefaultLanguage();
        $this->language = Settings::resolveLanguage($language) ?? '';
        $this->previousSubject = Request::postString('subject') ?? '';

        if (trim($this->draft) === '' || \strlen($this->draft) > 30000) {
            $this->setError($this->translation('ai_subject_requires_content'));
        }
        if (!\in_array($this->language, array_values(Settings::getLanguages()), true)) {
            $this->setError($this->translation('ai_validation_error_invalid_language'));
        }
        if (\strlen($this->previousSubject) > 255 || preg_match('/[\r\n]/', $this->previousSubject)) {
            $this->setError($this->translation('ai_validation_error_invalid_input_subject'));
        }
    }

    public function handler(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $this->validate();
        if ($this->hasErrors()) {
            echo json_encode(['status' => 'error', 'message' => $this->getErrors()[0]]);

            return;
        }

        try {
            $request = RequestData::make('', '', $this->draft, null, null, null, $this->language);
            $subject = AIEmail::generateSubject($request, $this->draft, $this->previousSubject);

            echo json_encode(['status' => 'success', 'subject' => $subject]);
        } catch (\Throwable $e) {
            error_log('AIComposePlugin subject generation failed: ' . \get_class($e));
            echo json_encode(['status' => 'error']);
        }
    }
}
