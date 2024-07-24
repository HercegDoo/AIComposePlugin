<?php

declare(strict_types=1);


namespace HercegDoo\AIComposePlugin\Tasks;

use HercegDoo\AIComposePlugin\Tasks\AbstractTask;

class MailTask extends AbstractTask
{

    public function init(): void
    {
        $this->plugin->add_hook('render_page', [$this, 'load_resources']);
    }


    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function load_resources(array $args): array
    {
        // TODO: Zašto postoji ovaj file settings.css u MailTask.php?
        $this->plugin->include_stylesheet('./css/settings.css');

        if (isset($args['template']) && $args['template'] == 'compose') {
            $this->plugin->include_stylesheet('./assets/styles/composeStyles.css');
            $this->plugin->include_script('./assets/scripts/composeMain.js');
        }

        return $args;
    }

}