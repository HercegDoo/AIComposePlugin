<?php

// test komentarr

namespace HercegDoo\AIComposePlugin;

use HercegDoo\AIComposePlugin\AIEmailService\Settings;
use HercegDoo\AIComposePlugin\Tasks\AbstractTask;

abstract class AbstractAIComposePlugin extends \rcube_plugin
{
    public $task = 'mail|settings';

    // test komentttddd
    public function init(): void
    {
        $this->add_hook('startup', [$this, 'getConf']);
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

    private function getConf(): void
    {
        $rcmail = \rcmail::get_instance();

        $this->load_config();

        // Defaults
        Settings::$default_input_chars = $rcmail->config->get('ai_defaultInputChars');
        Settings::$default_timeout = $rcmail->config->get('ai_defaultTimeout');
        Settings::$default_max_tokens = $rcmail->config->get('ai_maxTokens');
        // Styles
        Settings::$STYLE_CASUAL = $rcmail->config->get('ai_compose_style_casual');
        Settings::$STYLE_PROFESSIONAL = $rcmail->config->get('ai_compose_style_casual');
        Settings::$STYLE_ASSERTIVE = $rcmail->config->get('ai_compose_style_assertive');
        Settings::$STYLE_ENTHUSIASTIC = $rcmail->config->get('ai_compose_style_enthusiastic');
        Settings::$STYLE_FUNNY = $rcmail->config->get('ai_compose_style_funny');
        Settings::$STYLE_INFORMATIONAL = $rcmail->config->get('ai_compose_style_informational');
        Settings::$STYLE_PERSUASIVE = $rcmail->config->get('ai_compose_style_persuasive');
        // Lengths
        Settings::$LENGTH_SHORT = $rcmail->config->get('ai_compose_length_short');
        Settings::$LENGTH_MEDIUM = $rcmail->config->get('ai_compose_length_medium');
        Settings::$LENGTH_LONG = $rcmail->config->get('ai_compose_length_long');
        // Creativities
        Settings::$CREATIVITY_LOW = $rcmail->config->get('ai_compose_length_low');
        Settings::$CREATIVITY_MEDIUM = $rcmail->config->get('ai_compose_length_medium');
        Settings::$CREATIVITY_HIGH = $rcmail->config->get('ai_compose_length_high');
        // Languages
        Settings::$LANGUAGE_BOSNIAN = $rcmail->config->get('ai_compose_language_bosnian');
        Settings::$LANGUAGE_CROATIAN = $rcmail->config->get('ai_compose_language_croatian');
        Settings::$LANGUAGE_ENGLISH = $rcmail->config->get('ai_compose_language_english');
        Settings::$LANGUAGE_GERMAN = $rcmail->config->get('ai_compose_language_german');
        Settings::$LANGUAGE_DUTCH = $rcmail->config->get('ai_compose_language_dutch');
    }
}
