<?php

namespace HercegDoo\AIComposePlugin\Actions;

use HercegDoo\AIComposePlugin\Utilities\TranslationTrait;
use HercegDoo\AIComposePlugin\Utilities\XSSProtection;

abstract class AbstractAction
{
    use TranslationTrait;
    public static \rcube_plugin $plugin;
    protected \rcmail $rcmail;

    /**
     * @var string[]
     */
    private array $errors = [];

    public function __construct()
    {
        $this->rcmail = \rcmail::get_instance();
    }

    public static function register(): void
    {
        $actionClass = static::class;
        $action = new $actionClass();
        self::$plugin->register_action(self::getActionSlug(), [$action, 'requestHandler']);
    }

    public static function getActionSlug(): string
    {
        $fullClassName = static::class;

        return 'plugin.AIComposePlugin_' . substr((string) strrchr($fullClassName, '\\'), 1);
    }

    public function requestHandler(): void
    {
        if ($this instanceof ValidateAction) {
            $this->validate();

            if ($this->hasErrors()) {
                // Retornar erros como JSON sanitizado em vez de mostrar mensagens
                header('Content-Type: application/json');
                $errorMessages = XSSProtection::escapeArray($this->getErrors());
                
                echo json_encode([
                    'status' => 'error',
                    'respond' => implode(', ', $errorMessages)
                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                exit;
            }
        }

        $this->handler();
        exit;
    }

    public function hasErrors(): bool
    {
        return $this->getErrors() !== [];
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    abstract protected function handler(): void;

    /**
     * @param string[] $errors
     */
    protected function setErrors(array $errors): self
    {
        $this->errors = [...$this->errors, ...$errors];

        return $this;
    }

    protected function setError(string $message): self
    {
        $this->setErrors([$message]);

        return $this;
    }
}
