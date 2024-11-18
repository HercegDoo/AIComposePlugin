<?php

namespace HercegDoo\AIComposePlugin\Utilities;

use HercegDoo\AIComposePlugin\Tasks\AbstractTask;

class ContentInjecter
{

    /**
     * @var mixed
     */
    private $aiPluginOptions;

    /**
     * @var mixed
     */
    private $selectFields;

    public function __construct()
    {
        $this->aiPluginOptions = (array) \rcmail::get_instance()->output->get_env('aiPluginOptions');
        $this->selectFields = [
            'language' => $this->aiPluginOptions['languages'],
            'creativity' => $this->aiPluginOptions['creativities'],
            'length' => $this->aiPluginOptions['lengths'],
            'style' => $this->aiPluginOptions['styles'],
        ];
    }



    /**
     * @param array<string, mixed> $baseHTML
     *
     * @return array<string, mixed>
     */
    public function insertContentAboveElement(array $baseHTML, string $contentToInsert, string $elementId): array
    {
        if (isset($baseHTML['content']) && \is_string($baseHTML['content']) && !str_contains($baseHTML['content'], $contentToInsert)) {
            $pattern = '/(<div\s+id="' . $elementId . '".*?>)/';

            if (str_contains($baseHTML['content'], 'id="' . $elementId . '"')) {
                $baseHTML['content'] = preg_replace($pattern, $contentToInsert . '$1', $baseHTML['content']);
            }
        }

        return $baseHTML;
    }

    public function createSelectElements(): string
    {
        $selectFields = '<div id="select-div">
        <div>
        <h4>' . $this->translation('ai_dialog_title') . '</h4>
        </div>';

        foreach ((array) $this->selectFields as $key => $values) {
            $selectFields .= '<div class="single-select">
        <div >
            <label for="' . $key . '">
                <span class="regular-size">' . $this->translation('ai_label_' . $key) . '</span>
            </label>
            <span class="xinfo right small-index"><div>' . $this->translation('ai_tip_' . $key) . '</div></span>
        </div>
        <select id="aic-' . $key . '" class="form-control pretty-select custom-select">
        ';

            foreach ((array) $values as $value) {
                $defaultValue = '';
                if (\is_array($this->aiPluginOptions) && isset($this->aiPluginOptions['default' . ucfirst($key)])) {
                    $defaultValue = $this->aiPluginOptions['default' . ucfirst($key)];
                }
                $value = \is_string($value) ? $value : '';
                $selectFields .= '<option value="' . $value . '" ' . $this->isSelected($value, $defaultValue) . '">' . ucfirst($value) . '</option>';
            }
            $selectFields .= '</select></div>';
        }

        $selectFields .= '</div>';

        return $selectFields;
    }

    /**
     * @param array<string, mixed> $baseHTML
     *
     * @return array<string, mixed>
     */
    public function add_buttons(string $buttonsToAdd, string $containerId, array $baseHTML): array
    {
        if (isset($baseHTML['content']) && \is_string($baseHTML['content']) && !str_contains($baseHTML['content'], $buttonsToAdd) && str_contains($baseHTML['content'], 'class="' . $containerId . '"')) {
            $baseHTML['content'] = preg_replace_callback(
                '/(<div\s+class="' . $containerId . '".*?>)(.*?)(<\/div>)/s',
                static function ($matches) use ($buttonsToAdd) {
                    // Pronađi postojeća dugmad unutar formbuttons div-a
                    preg_match_all('/<button.*?>.*?<\/button>/', $matches[2], $buttons);

                    // Dodaj nova dugmad između postojećih ili ako nema dugmadi, dodaj ih na početak
                    $middle_buttons = $buttons[0]
                        ? $buttons[0][0] . $buttonsToAdd . implode('', \array_slice($buttons[0], 1))
                        : $buttonsToAdd;

                    return $matches[1] . $middle_buttons . $matches[3];
                },
                $baseHTML['content']
            );
        }

        return $baseHTML;
    }

    public function updateTextContent($args, $idValues) {
        if (isset($args['content']) && is_string($args['content'])) {
            // Niz za praćenje koji su ID-ovi već promenjeni
            $changedIds = [];

            // Iteriraj kroz svaki element u nizu id => value
            foreach ($idValues as $id => $value) {
                // Proveri da li je ovaj ID već promenjen
                if (in_array($id, $changedIds)) {
                    error_log("ID $id je već promenjen, preskačem.");
                    continue;
                }

                // Proveri ako postoji element sa tim ID-om u sadržaju
                $pattern = '/(<[^>]+id=["\']' . preg_quote($id, '/') . '["\'][^>]*>)(.*?)(<\/[^>]+>)/s';

                if (preg_match($pattern, $args['content'], $matches)) {
                    // Proveri da li je tekst već izmenjen
                    if (trim($matches[2]) === trim($value)) {
                        error_log("Tekst za ID: $id je već postavljen na željenu vrednost ($value), preskačem.");
                        continue;
                    }

                    // Dodaj ID u niz promenjenih ID-ova
                    $changedIds[] = $id;

                    // Poziv funkcije za prevođenje
                    $translatedValue = $this->translation($value);

                    // Loguj pretragu sadržaja
                    error_log("Pronađen sadržaj za ID: " . $matches[2]);

                    // Koristi preg_replace_callback za zamenu sadržaja unutar tagova
                    $args['content'] = preg_replace_callback(
                        $pattern,
                        function ($match) use ($translatedValue) {
                            error_log("ono sto mi vraca " . print_r($match[1] . $translatedValue . $match[3], true));
                            return $match[1] . $translatedValue . $match[3];
                        },
                        $args['content']
                    );

                    // Loguj promene
                    error_log("Promenjen tekst za ID: $id. Novi tekst: $translatedValue.");
                } else {
                    error_log("Element sa ID: $id nije pronađen.");
                }
            }
        }

        return $args;
    }






    protected function translation(string $key): string
    {
        return \rcmail::get_instance()->gettext("AIComposePlugin.{$key}");
    }

    /**
     * @param mixed|string $value
     * @param mixed|string $defaultValue
     */
    private function isSelected($value, $defaultValue): string
    {
        return $value === $defaultValue ? 'selected' : '';
    }


}
