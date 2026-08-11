<?php

namespace HercegDoo\AIComposePlugin\Actions\Settings;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;
use HercegDoo\AIComposePlugin\Actions\ValidateAction;
use HercegDoo\AIComposePlugin\Utilities\XSSProtection;
use HercegDoo\AIComposePlugin\Utilities\PromptInjectionProtection;
use HercegDoo\AIComposePlugin\Utilities\RateLimiter;

class SaveInstruction extends AbstractAction implements ValidateAction
{
    public function validate(): void
    {
        $id = trim(\rcube_utils::get_input_string('_id', \rcube_utils::INPUT_POST));
        $name = trim(\rcube_utils::get_input_string('_name', \rcube_utils::INPUT_POST));
        $text = trim(\rcube_utils::get_input_string('_text', \rcube_utils::INPUT_POST));
        
        // VALIDAÇÃO OBRIGATÓRIA CONTRA PROMPT INJECTION
        if ($name !== null) {
            $nameValidation = PromptInjectionProtection::validateAndSanitize($name, true);
            if (!$nameValidation['valid']) {
                $this->setError($this->translation('ai_validation_error_malicious_content_detected'));
                return;
            }
            $name = $nameValidation['sanitized'];
        }
        
        if ($text !== null) {
            $textValidation = PromptInjectionProtection::validateAndSanitize($text, true);
            if (!$textValidation['valid']) {
                $this->setError($this->translation('ai_validation_error_malicious_content_detected'));
                return;
            }
            $text = $textValidation['sanitized'];
        }
        
        // Sanitização XSS adicional
        $name = XSSProtection::escape($name);
        $text = XSSProtection::escape($text);

        if (empty($name) || empty($text)) {
            $this->rcmail->output->command('addinstructiontemplate', $id);
            $this->setError($this->translation('ai_predefined_invalid_input'));
        }
    }

    /**
     * @param array<string, string> $args
     */
    protected function handler(array $args = []): void
    {
        // Rate limiting para prevenir abuso
        $identifier = RateLimiter::generateIdentifier();
        $rateLimitResult = RateLimiter::isAllowed($identifier, 'instruction_save');
        
        if (!$rateLimitResult['allowed']) {
            $this->rcmail->output->show_message(
                $this->translation('ai_rate_limit_exceeded'), 
                'error'
            );
            $this->rcmail->output->send('iframe');
            return;
        }
        
        $name = trim(\rcube_utils::get_input_string('_name', \rcube_utils::INPUT_POST));
        $text = trim(\rcube_utils::get_input_string('_text', \rcube_utils::INPUT_POST));
        $id = trim(\rcube_utils::get_input_string('_id', \rcube_utils::INPUT_POST));
        
        // Sanitizar dados para prevenir XSS (já feito na validação)
        $name = XSSProtection::escape($name);
        $text = XSSProtection::escape($text);
        $id = XSSProtection::escape($id);
        
        $predefinedInstructionsLimit = $this->getInstructionsLimit();
        $predefinedInstructions = $this->rcmail->user->get_prefs()['predefinedInstructions'] ?? [];

        if (!empty($id)) {
            $this->updateInstruction($predefinedInstructions, $id, $name, $text);
        } else {
            if ($this->isInstructionLimitReached($predefinedInstructions, $predefinedInstructionsLimit)) {
                return;
            }

            $id = $this->addNewInstruction($predefinedInstructions, $name, $text);
        }

        $this->rcmail->output->show_message($this->translation('ai_predefined_successful_save'), 'confirmation');
        $this->rcmail->output->command('parent.updateinstructionlist', $id, $name);
        $this->rcmail->output->command('addinstructiontemplate', $id);
        $this->rcmail->user->save_prefs(['predefinedInstructions' => $predefinedInstructions]);
        $this->rcmail->output->send('iframe');
    }

    /**
     * @param array<int, array<string,string>> $predefinedInstructions
     */
    private function updateInstruction(array &$predefinedInstructions, string $id, string $name, string $text): void
    {
        foreach ($predefinedInstructions as &$predefinedInstruction) {
            if (str_contains($id, $predefinedInstruction['id'])) {
                $predefinedInstruction['title'] = $name;
                $predefinedInstruction['message'] = $text;
            }
        }
        unset($predefinedInstruction);
    }

    /**
     * @param array<int, array<string,string>> $predefinedInstructions
     */
    private function addNewInstruction(array &$predefinedInstructions, string $name, string $text): string
    {
        $response = [
            'title' => $name,
            'message' => $text,
            'id' => uniqid(),
        ];
        $predefinedInstructions[] = $response;

        return $response['id'];
    }

    /**
     * @param array<int, array<string,string>> $predefinedInstructions
     */
    private function isInstructionLimitReached(array $predefinedInstructions, int $limit): bool
    {
        if (\count($predefinedInstructions) >= $limit) {
            $this->rcmail->output->show_message($this->translation('ai_predefined_max_instructions_error'), 'error');
            $this->rcmail->output->command('addinstructiontemplate');
            $this->rcmail->output->send('iframe');

            return true;
        }

        return false;
    }

    private function getInstructionsLimit(): int
    {
        $predefinedInstructionsLimit = $this->rcmail->config->get('aiMaxPredefinedInstructions');
        if (is_numeric($predefinedInstructionsLimit)) {
            $predefinedInstructionsLimit = (int) $predefinedInstructionsLimit;
        } else {
            $predefinedInstructionsLimit = 20;
        }

        return $predefinedInstructionsLimit;
    }
}
