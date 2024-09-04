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

        //        $request = RequestData::make('muhi', 'meho', 'dobro jutro');
        //        Settings::setCreativity('low');
        //        $def_cr = Settings::getCreativity();
        //        $request->setCreativity($def_cr);
        //        $email = AIEmail::generate($request);
        //        print_r($email->getBody());

        $this->add_hook('startup', [$this, 'startup']);
        $task = $this->api->task;

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

    public function startup(): void
    {
        $rcmail = \rcmail::get_instance();
        $settings = [Settings::getLanguages(), Settings::getLengths(), Settings::getCreativities(), Settings::getStyles()];
        $rcmail->output->set_env('aiPluginDropdownOptions', $settings);
    }

    private function initSettings(): void
    {
        $rcmail = \rcmail::get_instance();

        $this->load_config();

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
    }
}
