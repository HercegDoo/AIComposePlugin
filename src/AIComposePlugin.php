<?php

namespace HercegDoo\AIComposePlugin;

final class AIComposePlugin extends \rcube_plugin
{
    public $task = 'mail|settings';

    public function init(): void
    {
        if ($this->api->task == 'settings') {
            $this->add_hook('preferences_sections_list', [$this, 'preferencesSectionsList']);
            $this->add_hook('preferences_list', [$this, 'preferencesList']);
        } elseif ($this->api->task == 'mail') {
            $this->add_hook('render_page', [$this, 'load_resources']);
        }
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function load_resources(array $args): array
    {
        $this->include_stylesheet('./css/settings.css');

        if (isset($args['template']) && $args['template'] == 'compose') {
            $this->include_stylesheet('./assets/styles/composeStyles.css');
            $this->include_script('./assets/scripts/composeMain.js');
        }

        return $args;
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function preferencesSectionsList(array $args): array
    {
        /** @var array<string, array<string, mixed>> $list */
        $list = $args['list'] ?? [];

        $list['aic'] = [
            'id' => 'aic',
            'section' => 'AICompose Settings',
        ];

        $args['list'] = $list;

        return $args;
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function preferencesList(array $args): array
    {
        /** @var array<string, array<string, mixed>> $blocks */
        $blocks = $args['blocks'] ?? [];

        if (isset($args['section']) && $args['section'] == 'aic') {
            $blocks['general'] = [
                'name' => 'General Settings',
                'options' => [
                    [
                        'title' => 'Option 1',
                        'content' => 'Content for Option 1',
                    ],
                    [
                        'title' => 'Option 2',
                        'content' => 'Content for Option 2',
                    ],
                ],
            ];

            $args['blocks'] = $blocks;
        }

        return $args;
    }
}
