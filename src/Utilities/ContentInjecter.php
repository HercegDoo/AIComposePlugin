<?php

namespace HercegDoo\AIComposePlugin\Utilities;

class ContentInjecter
{
    private $aiPluginOptions;

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


    protected function translation(string $key): string
    {
        return \rcmail::get_instance()->gettext("AIComposePlugin.{$key}");
    }

}
