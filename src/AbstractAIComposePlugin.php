<?php

namespace HercegDoo\AIComposePlugin;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;
use HercegDoo\AIComposePlugin\Tasks\AbstractTask;

abstract class AbstractAIComposePlugin extends \rcube_plugin
{
    public $task = 'mail|settings';
    protected \rcmail $rcmail;

    public function init(): void
    {
        $this->rcmail = \rcmail::get_instance();
        $task = $this->api->task;
        // register plugin attribute for actions, IMPORTNAT
        AbstractAction::$plugin = $this;

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
}
