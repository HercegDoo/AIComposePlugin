<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tasks;

use HercegDoo\AIComposePlugin\AIEmailService\Settings;

class MailTask extends AbstractTask
{
    public function init(): void
    {
        $this->plugin->add_hook('startup', [$this, 'startup']);
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
            $this->plugin->include_script('assets/dist/compose.bundle.js');
        }

        return $args;
    }

    public function startup(): void
    {
        $rcmail = \rcmail::get_instance();
        $settings = [
            'languages' => array_values(Settings::getLanguages()),
            'defaultLanguage' => Settings::getDefaultLanguage(),
            'lengths' => array_values(Settings::getLengths()),
            'defaultLength' => Settings::getDefaultLength(),
            'creativities' => array_values(Settings::getCreativities()),
            'defaultCreativity' => Settings::getCreativity(),
            'styles' => array_values(Settings::getStyles()),
            'defaultStyle' => Settings::getDefaultStyle(),
        ];

        if ($rcmail->action === 'compose') {
            $this->loadTranslations();
            $rcmail->output->set_env('aiPluginOptions', $settings);
            $rcmail->output->set_env('aiPredefinedInstructions', $rcmail->user->get_prefs()['predefinedInstructions'] ?? []);
        }
    }
}
