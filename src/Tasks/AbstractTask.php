<?php

declare(strict_types=1);


namespace HercegDoo\AIComposePlugin\Tasks;

use rcube_plugin;
use rcube_plugin_api;

abstract class AbstractTask
{
    protected rcube_plugin $plugin;
    protected ?rcube_plugin_api $api = null;
    public function __construct(rcube_plugin $plugin)
    {
        $this->plugin = $plugin;
        $this->api = $plugin->api;

        $this->init();
    }

    abstract public function init(): void;
}