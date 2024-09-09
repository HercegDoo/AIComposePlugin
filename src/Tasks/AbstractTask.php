<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tasks;

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
        $this->init();
    }

    abstract public function init(): void;

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
    }
}
