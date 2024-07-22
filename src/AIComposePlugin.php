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

        $this->include_stylesheet('./css/settings.css');

        if ($args['template'] == 'compose' ) {
            $this->include_stylesheet('./assets/styles/composeStyles.css');
            $this->include_script('./assets/scripts/composeMain.js');

        }elseif ($args['template'] == 'settings') {
            $this->include_stylesheet('./assets/styles/settingsStyles.css');
            $this->include_script('./assets/scripts/settingsMain.js');

        }

        return $args;
    }
}
