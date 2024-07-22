<?php
class AIComposePlugin extends rcube_plugin
{
public $task = 'mail';

function init()
{
$this->add_hook('render_page', array($this, 'load_resources'));
}

function load_resources($args)
{
if ($args['template'] == 'compose')
{
$this->include_stylesheet('./assets/styles/styles.css');
$this->include_stylesheet('/assets/styles/modal.css');
$this->include_script('./assets/scripts/backend_communication.js');
$this->include_script('./assets/scripts/modal.js');
$this->include_script('./assets/scripts/main.js');
}
return $args;
}
}