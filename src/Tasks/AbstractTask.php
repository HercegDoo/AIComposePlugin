<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tasks;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;

abstract class AbstractTask
{
    protected \rcube_plugin $plugin;
    protected ?\rcube_plugin_api $api = null;

    public function __construct(\rcube_plugin $plugin)
    {
        $this->plugin = $plugin;
        $this->api = $plugin->api;

        $this->initSettings();
        $this->autoRegisterActions();
        $this->init();
    }

    abstract public function init(): void;

    protected function loadTranslations(): void
    {
        $this->plugin->add_texts('src/localization/messages/');
        $this->plugin->add_texts('src/localization/labels/', true);
    }

    private function initSettings(): void
    {
        $rcmail = \rcmail::get_instance();
        $this->plugin->load_config();

        /** @var int $defaultTimeout */
        $defaultTimeout = $rcmail->config->get('aiDefaultTimeout', 60);
        Settings::setDefaultTimeout($defaultTimeout);

        /** @var int $defaultInputChars */
        $defaultInputChars = $rcmail->config->get('aiDefaultInputChars', 500);
        Settings::setDefaultInputChars($defaultInputChars);

        /** @var int $defaultMaxTokens */
        $defaultMaxTokens = $rcmail->config->get('aiDefaultMaxTokens', 2000);
        Settings::setDefaultMaxTokens($defaultMaxTokens);

        /** @var string[] $styles */
        $styles = $rcmail->config->get('aiComposeStyles', [
            'professional',
            'default' => 'casual',
            'assertive',
            'enthusiastic',
            'funny',
            'informational',
            'persuasive',
        ]);
        Settings::setStyles($styles);

        /** @var string[] $lengths */
        $lengths = $rcmail->config->get('aiComposeLengths', [
            'short',
            'default' => 'medium',
            'long',
        ]);

        Settings::setLengths($lengths);

        /** @var string[] $languages */
        $languages = $rcmail->config->get('aiComposeLanguages', [
            'default' => 'Bosnian',
            'Croatian',
            'German',
            'Dutch',
        ]);

        Settings::setLanguages($languages);

        /** @var string $creativity */
        $creativity = $rcmail->config->get('aiComposeCreativity');
        if ($creativity !== null) {
            Settings::setCreativity($creativity);
        }

        /** @var string $provider */
        $provider = $rcmail->config->get('aiComposeProvider', 'OpenAI');

        Settings::setProvider($provider);

        /** @var array<string> $config */
        $config = $rcmail->config->get('aiProvider' . $provider . 'Config', []);
        Settings::setProviderConfig($config);

        $rcmail->output->set_env('aiPredefinedInstructions', $rcmail->user->get_prefs()['predefinedInstructions'] ?? []);
    }

    private function autoRegisterActions(): void
    {
        $task = $this->api->task;

        if (\is_string($task)) {
            $task = ucfirst($task);
            $dirActions = implode(\DIRECTORY_SEPARATOR, [__DIR__, '..', 'Actions', $task]);

            if (!is_dir($dirActions)) {
                return;
            }

            $files = scandir($dirActions);

            if (\is_array($files)) {
                foreach ($files as $file) {
                    if (\in_array($file, ['.', '..'])) {
                        continue;
                    }

                    $className = basename($file, '.php');
                    $taskClass = "HercegDoo\\AIComposePlugin\\Actions\\{$task}\\{$className}";

                    if (class_exists($taskClass) && is_subclass_of($taskClass, AbstractAction::class)) {
                        $taskClass::register();
                    }
                }
            }
        }
    }
}
