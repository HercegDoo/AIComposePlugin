<?php

namespace HercegDoo\AIComposePlugin;

use HercegDoo\AIComposePlugin\AIEmailService\AIEmail;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use HercegDoo\AIComposePlugin\Tasks\AbstractTask;

abstract class AbstractAIComposePlugin extends \rcube_plugin
{
    public $task = 'mail|settings';

    public function init(): void
    {
        $this->initSettings();

        $task = $this->api->task;

        $request = RequestData::make('muhi', 'meho', 'dobro jutro');
        $email = AIEmail::generate($request);
        print_r($email->getBody());

        if (\is_string($task)) {
            $task = ucfirst($task);
            $taskClass = "HercegDoo\\AIComposePlugin\\Tasks\\{$task}Task";

            if (class_exists($taskClass)) {
                /** @var AbstractTask $taskHandler */
                $taskHandler = new $taskClass($this);
                $taskHandler->init();
            }
        }
    }

    private function initSettings(): void
    {
        $rcmail = \rcmail::get_instance();

        $this->load_config();

        /** @var int $default_timeout */
        $default_timeout = $rcmail->config->get('ai_default_timeout', 60);
        Settings::setDefaultTimeout($default_timeout);

        /** @var int $default_input_chars */
        $default_input_chars = $rcmail->config->get('ai_default_input_chars', 500);
        Settings::setDefaultInputChars($default_input_chars);

        /** @var int $default_max_tokens */
        $default_max_tokens = $rcmail->config->get('ai_default_max_tokens', 2000);
        Settings::setDefaultMaxTokens($default_max_tokens);

        /** @var string[] $styles */
        $styles = $rcmail->config->get('ai_compose_styles', [
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
        $lengths = $rcmail->config->get('ai_compose_lengths', [
            'short',
            'default' => 'medium',
            'long',
        ]);

        Settings::setLengths($lengths);

        /** @var string[] $languages */
        $languages = $rcmail->config->get('ai_compose_languages', [
            'default' => 'Bosnian',
            'Croatian',
            'German',
            'Dutch',
        ]);

        Settings::setLanguages($languages);

        /** @var string $default_creativity */
        $default_creativity = $rcmail->config->get('ai_compose_default_creativity');
        Settings::setDefaultCreativity($default_creativity);

        /** @var string $provider */
        $provider = $rcmail->config->get('ai_compose_provider', 'openai');

        Settings::setProvider($provider);

        /** @var array<string> $config */
        $config = $rcmail->config->get('ai_provider_' . $provider . '_config', []);
        Settings::setProviderConfig($config);
    }
}
