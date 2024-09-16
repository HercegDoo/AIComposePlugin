<?php

namespace HercegDoo\AIComposePlugin\Actions;

abstract class AbstractAction
{
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
        $this->validate();

        if ($this->hasErrors()) {
            foreach ($this->getErrors() as $error) {
                $this->rcmail->output->show_message($error, 'error');
            }

            // Prekini izvršavanje ako su podaci nevalidni
            $this->rcmail->output->send();

            return;
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

    abstract protected function validate(): void;
}
