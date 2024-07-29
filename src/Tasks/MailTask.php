<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tasks;

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
        if (isset($args['template']) && $args['template'] == 'compose') {
            $this->plugin->include_stylesheet('assets/dist/compose.bundle.js');
        }

        return $args;
    }
}
