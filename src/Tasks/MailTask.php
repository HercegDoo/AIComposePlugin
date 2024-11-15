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
        $this->plugin->add_hook('render_page', [$this, 'add_instruction_field']);
        $this->plugin->add_hook('render_page', [$this, 'add_form_buttons']);
        $this->plugin->add_hook('render_page', [$this, 'add_select_fields']);
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

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function add_instruction_field(array $args): array
    {
        $new_content = '<div id="aic-instructions-div" class="form-group row">
					<label for="_to" class="col-2 col-form-label">' . $this->translation('ai_label_instructions') . '</label>
					<div class="col-10">
						<div class="input-group">
							<textarea name="aic-instructions" spellcheck="false" id="aic-instructions-textarea" tabindex="-1" data-recipient-input="true" style="position: absolute; opacity: 0; left: -5000px; width: 10px;" autocomplete="off" aria-autocomplete="list" aria-expanded="false" role="combobox"></textarea>
							<span class="input-group-append">
								<a href="#"  class="input-group-text add icon" title="Placeholder" tabindex="1"><span class="inner">Placeholder</span></a>
							</span>
							<span class="input-group-append">
								<a href="#" data-popup="headers-menu" class="input-group-text icon add active" title="PLACEHOLDER" tabindex="1" aria-haspopup="true" aria-expanded="false" aria-owns="headers-menu" data-original-title="PLACEHOLDER"><span class="inner">PLACEHOLDER</span></a>
							</span>
						</div>
					</div>
				</div>';

        if (isset($args['content']) && \is_string($args['content']) && !str_contains($args['content'], $new_content)) {
            $pattern = '/(<div\s+id="composebodycontainer".*?>)/';

            if (str_contains($args['content'], 'id="composebodycontainer"')) {
                $args['content'] = preg_replace($pattern, $new_content . '$1', $args['content']);
            }
        }

        return $args;
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function add_form_buttons(array $args): array
    {
        $new_buttons = '<button type="button"  id="generate-email-button" class="btn btn-primary form-buttons">
      <span id="generate-email-span" >' . $this->translation('ai_generate_email') . '</span>
      <span id="generate-again-span" style="display: none;">' . $this->translation('ai_generate_again') . '</span>
  </button>
  <button type="button" class="btn btn-default form-buttons" id="instruction-example" >' . $this->translation('ai_button_show_instructions') . '</button>';

        if (isset($args['content']) && \is_string($args['content']) && !str_contains($args['content'], $new_buttons) && str_contains($args['content'], 'class="formbuttons"')) {
            $args['content'] = preg_replace_callback(
                '/(<div\s+class="formbuttons".*?>)(.*?)(<\/div>)/s',
                static function ($matches) use ($new_buttons) {
                    // Pronađi postojeća dugmad unutar formbuttons div-a
                    preg_match_all('/<button.*?>.*?<\/button>/', $matches[2], $buttons);

                    // Dodaj nova dugmad između postojećih ili ako nema dugmadi, dodaj ih na početak
                    $middle_buttons = $buttons[0]
                        ? $buttons[0][0] . $new_buttons . implode('', \array_slice($buttons[0], 1))
                        : $new_buttons;

                    return $matches[1] . $middle_buttons . $matches[3];
                },
                $args['content']
            );
        }

        return $args;
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function add_select_fields(array $args): array
    {
        $generatedSelectFields = $this->create_select_fields();

        if (isset($args['content']) && \is_string($args['content']) && !str_contains($args['content'], $generatedSelectFields)) {
            $pattern = '/(<div\s+id="compose-attachments".*?>)/';

            if (str_contains($args['content'], 'id="compose-attachments"')) {
                $args['content'] = preg_replace($pattern, $generatedSelectFields . '$1', $args['content']);
            }
        }

        return $args;
    }

    public function create_select_fields(): string
    {
        $rcmail = \rcmail::get_instance();
        $aiPluginOptions = $rcmail->output->get_env('aiPluginOptions');
        $languages = [];
        $creativities = [];
        $lengths = [];
        $styles = [];

        if (\is_array($aiPluginOptions)) {
            $languages = $aiPluginOptions['languages'];
            $creativities = $aiPluginOptions['creativities'];
            $lengths = $aiPluginOptions['lengths'];
            $styles = $aiPluginOptions['styles'];
        }

        $fields = [
            'language' => $languages,
            'creativity' => $creativities,
            'length' => $lengths,
            'style' => $styles,
        ];

        $new_content = '<div id="select-div">
        <div>
        <h4>' . $this->translation('ai_dialog_title') . '</h4>
        </div>';

        foreach ($fields as $key => $values) {
            $new_content .= '<div class="single-select">
        <div >
            <label for="' . $key . '">
                <span class="regular-size">' . $this->translation('ai_label_' . $key) . '</span>
            </label>
            <span class="xinfo right small-index"><div>' . $this->translation('ai_tip_' . $key) . '</div></span>
        </div>
        <select id="aic-' . $key . '" class="form-control pretty-select custom-select">
        ';

            foreach ($values as $value) {
                $checkValue = '';
                if (\is_array($aiPluginOptions)) {
                    $checkValue = $aiPluginOptions['default' . ucfirst($key)];
                }
                $new_content .= '<option value="' . $value . '" ' . $this->isSelected($value, $checkValue) . '">' . ucfirst($value) . '</option>';
            }
            $new_content .= '</select></div>';
        }

        $new_content .= '</div>';

        return $new_content;
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

    private function isSelected(string $value, string $defaultValue): string
    {
        return $value === $defaultValue ? 'selected' : '';
    }
}
