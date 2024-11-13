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
        $this->plugin->add_hook('render_page', array($this, 'add_instruction_field'));
        $this->plugin->add_hook('render_page', array($this, 'add_form_buttons'));


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

    public function add_instruction_field(array $args): array
    {
        $new_content = '<div id="aic-instructions-div" class="form-group row">
					<label for="_to" class="col-2 col-form-label">' . $this->translation('ai_label_instructions') .'</label>
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

        if (strpos($args['content'], $new_content) === false) {
            $pattern = '/(<div\s+id="composebodycontainer".*?>)/';


            if (strpos($args['content'], 'id="composebodycontainer"') !== false) {
                $args['content'] = preg_replace($pattern, $new_content . '$1', $args['content']);
            }

        }
        return $args;
    }

    public function add_form_buttons(array $args): array
    {
        $new_buttons = '<button type="button" class="btn btn-primary" id="button1">Dugme 1</button>
                    <button type="button" class="btn btn-secondary" id="button2">Dugme 2</button>';


        if (strpos($args['content'], $new_buttons) === false) {

            // Provera da li 'formbuttons' div već postoji u sadržaju
            if (strpos($args['content'], 'class="formbuttons"') !== false) {

                // Regularni izraz koji traži sve dugmadi unutar formbuttons div-a
                $args['content'] = preg_replace_callback('/(<div\s+class="formbuttons".*?>)(.*?)(<\/div>)/s', function ($matches) use ($new_buttons) {

                    // Svi dugmadi unutar formbuttons div-a
                    preg_match_all('/<button.*?>(.*?)<\/button>/', $matches[2], $buttons);

                    // Ako imamo najmanje dva dugmadi, umetnemo nova između
                    if (count($buttons[0]) > 0) {
                        // Prvo dugme, srednji deo (postojeća dugmadi), i poslednje dugme
                        $middle_buttons = implode('', array_slice($buttons[0], 0, 1)) . $new_buttons . implode('', array_slice($buttons[0], 1));
                        return $matches[1] . $middle_buttons . $matches[3];
                    }

                    // Ako nema dugmadi, samo dodajemo nova
                    return $matches[1] . $new_buttons . $matches[3];
                }, $args['content']);
            }
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
