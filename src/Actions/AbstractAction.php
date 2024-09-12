<?php

namespace HercegDoo\AIComposePlugin\Actions;

abstract class AbstractAction
{
    public static \rcube_plugin $plugin;
    protected \rcmail $rcmail;

    private array $errors = [];

    public function __construct()
    {
        $this->rcmail = \rcmail::get_instance();
    }

    public static function register(): void
    {
        $actionClass = get_called_class();
        $action = new $actionClass();
        self::$plugin->register_action(self::getActionSlug(), [$action, 'requestHandler']);
    }


    public static function getActionSlug(): string
    {
        $fullClassName = get_called_class();
        return 'plugin.AIComposePlugin_' . substr((string) strrchr($fullClassName, '\\'), 1);
    }

    abstract protected function handler(): void;


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

    }

    public function hasErrors(): bool
    {
        return $this->getErrors() !== [];
    }

    /**
     * @return  string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

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