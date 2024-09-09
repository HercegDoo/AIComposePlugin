<?php

namespace HercegDoo\AIComposePlugin;

use HercegDoo\AIComposePlugin\Tasks\AbstractTask;

abstract class AbstractAIComposePlugin extends \rcube_plugin
{
    public $task = 'mail|settings';

    public function init(): void
    {
        $task = $this->api->task;

        if (\is_string($task)) {
            $this->loadTranslations();

            $task = ucfirst($task);
            $taskClass = "HercegDoo\\AIComposePlugin\\Tasks\\{$task}Task";

            if (class_exists($taskClass)) {
                /** @var AbstractTask $taskHandler */
                $taskHandler = new $taskClass($this);
                $taskHandler->init();
            }
        }
    }

    private function loadTranslations(): void
    {
        $this->add_texts('src/localization/messages/');
        $this->add_texts('src/localization/labels/', true);
    }
}
