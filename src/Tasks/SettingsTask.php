<?php

declare(strict_types=1);

namespace HercegDoo\AIComposePlugin\Tasks;

use HercegDoo\AIComposePlugin\AIEmailService\Settings;

class SettingsTask extends AbstractTask
{
    public function init(): void
    {
        $this->plugin->add_hook('preferences_sections_list', [$this, 'preferencesSectionsList']);
        $this->plugin->add_hook('preferences_list', [$this, 'preferencesList']);
        $this->plugin->add_hook('preferences_save', [$this, 'preferencesSave']);
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
        $this->plugin->include_script('assets/dist/settings.bundle.js');
        /** @var array<string, array<string, mixed>> $blocks */
        $blocks = $args['blocks'] ?? [];

        if (isset($args['section']) && $args['section'] == 'aic') {
            $blocks['general'] = [
                'name' => 'General Settings',
                'options' => [
                    [
                        'title' => 'Style',
                        'content' => $this->getDropdownHtml(Settings::getStyles(), 'style', Settings::getDefaultStyle()),
                    ],
                    [
                        'title' => 'Creativity',
                        'content' => $this->getDropdownHtml(Settings::getCreativities(), 'creativity', Settings::getCreativity()),
                    ],
                    [
                        'title' => 'Length',
                        'content' => $this->getDropdownHtml(Settings::getLengths(), 'length', Settings::getDefaultLength()),
                    ],
                    [
                        'title' => 'Language',
                        'content' => $this->getDropdownHtml(Settings::getLanguages(), 'language', Settings::getDefaultLanguage()),
                    ],
                ],
            ];

            $args['blocks'] = $blocks;
        }

        return $args;
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function preferencesSave(array $args): array
    {
        if ($args['section'] === 'aic') {
            $data = \rcube_utils::get_input_value('data', \rcube_utils::INPUT_POST);
            $aicData = [];
            if (\is_array($data) && isset($data['aic'])) {
                $aicData = $data['aic'];
            }
            $rcmail = \rcmail::get_instance();

            if ($this->validateSettingsValues($aicData['style'], Settings::getStyles()) && $this->validateSettingsValues($aicData['creativity'], Settings::getCreativities())
                && $this->validateSettingsValues($aicData['length'], Settings::getLengths()) && $this->validateSettingsValues($aicData['language'], Settings::getLanguages())
            ) {
                $rcmail->user->save_prefs([
                    'aicDefaults' => $aicData,
                ]);
            }
        }

        return $args;
    }

    /**
     * @param string[] $options
     */
    private function getDropdownHtml(array $options, string $name, ?string $default = null): string
    {
        $dropdown = '<select name="data[aic][' . $name . ']">'; // Ispravno ime za formu
        foreach ($options as $option) {
            $dropdown .= '<option ' . ($option === $default ? 'selected' : '') . ' value="' . ($option) . '">' . ($this->translation('ai_' . $name . '_' . strtolower($option))) . '</option>';
        }
        $dropdown .= '</select>';

        return $dropdown;
    }

    /**
     * @param string[] $values
     */
    private function validateSettingsValues(string $selectedValue, array $values): bool
    {
        return \in_array($selectedValue, $values, true);
    }

    private function translation(string $key): string
    {
        return \rcmail::get_instance()->gettext("AIComposePlugin.{$key}");
    }
}
