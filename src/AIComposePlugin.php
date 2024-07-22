<?php

namespace HercegDoo\AIComposePlugin;

class AIComposePlugin extends rcube_plugin
{
    public $task = 'mail';

    public function init()
    {
        $this->add_hook('render_page', [$this, 'load_resources']);
    }

    public function load_resources($args)
    {
        if ($args['template'] == 'compose' || $args['template'] == 'settings') {
            $this->include_stylesheet('./assets/styles/styles.css');
            $this->include_script('./assets/scripts/main.js');
            $this->include_stylesheet('./css/all.css');
        }

        return $args;
    }
}
