<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tasks;

use HercegDoo\AIComposePlugin\Actions\GenereteEmailAction;
use HercegDoo\AIComposePlugin\AIEmailService\Settings;

class MailTask extends AbstractTask
{
    public function init(): void
    {
        $this->plugin->add_hook('startup', [$this, 'startup']);
        $this->plugin->add_hook('render_page', [$this, 'load_resources']);
        $this->plugin->register_action('plugin.aicGetAllInstructions', [$this, 'aicGetAllInstructions']);

        GenereteEmailAction::register();
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
        $rcmail->output->set_env('aiPluginOptions', $settings);
    }

    public function aicGetAllInstructions(): void
    {
        $rcmail = \rcmail::get_instance();
        header('Content-Type: application/json');

        try {
            $predefinedInstructions = $rcmail->user->get_prefs()['predefinedInstructions'] ?? [];

            echo json_encode([
                'status' => 'success',
                'returnValue' => $predefinedInstructions,
            ]);
        } catch (\Throwable $e) {
            error_log('Error message: (Get All Messages) ' . $e->getMessage());
            error_log('Error code (Get All Messages) : ' . $e->getCode());
            error_log('Error file (Get All Messages) : ' . $e->getFile());
            error_log('Error line (Get All Messages) : ' . $e->getLine());

            echo json_encode([
                'status' => 'error',
                'message' => $this->translation('ai_predefined_get_all_instructions_error'),
            ]);
        }

        exit();
    }

    private function translation(string $key): string
    {
        return \rcmail::get_instance()->gettext("AIComposePlugin.{$key}");
    }
}
