<?php

namespace HercegDoo\AIComposePlugin;

use HercegDoo\AIComposePlugin\Tasks\AbstractTask;

final class AIComposePlugin extends \rcube_plugin
{
    public $task = 'mail|settings';

    public function init(): void
    {
        $task = $this->api->task;

        if (is_string($task)) {
            $task = ucfirst($task);
            $taskClass = "HercegDoo\\AIComposePlugin\\Tasks\\{$task}Task";

            if (class_exists($taskClass)) {
                /** @var AbstractTask $taskHandler */
                $taskHandler = new $taskClass($this);
                $taskHandler->init();
            }
        }

    }
}
