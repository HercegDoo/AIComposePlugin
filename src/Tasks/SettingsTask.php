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
        error_log("EVO ERRORAAAA");
        error_log(print_r($args, true));
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
                        'content' => $this->getDropdownHtml(Settings::getStyles(), 'style'),
                    ],
                    [
                        'title' => 'Creativity',
                        'content' => $this->getDropdownHtml(Settings::getCreativities(), 'creativity'),
                    ],
                    [
                        'title' => 'Length',
                        'content' => $this->getDropdownHtml(Settings::getLengths(), 'length'),
                    ],
                    [
                        'title' => 'Language',
                        'content' => $this->getDropdownHtml(Settings::getLanguages(), 'language'),
                    ]
                ],
            ];

            $args['blocks'] = $blocks;
        }

        return $args;
    }



    private function getDropdownHtml(array $options, string $name): string
    {
        $dropdown = '<select name="data[aic][' . $name . ']">'; // Ispravno ime za formu
        foreach ($options as $option) {
            $dropdown .= '<option value="' . htmlspecialchars($option) . '">' . htmlspecialchars($option) . '</option>';
        }
        $dropdown .= '</select>';
        return $dropdown;
    }


    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    public function preferencesSave(array $args): array
    {
        error_log("POCETNI");
        error_log(print_r($args, true)); // Prikazivanje sadržaja args

        // Inicijalizuj prazan niz za sačuvane opcije
        $savedOptions = [];

        // Proveri da li 'data' postoji i da je niz
        if (isset($args['data']) && is_array($args['data'])) {

            error_log("JOS VANJSKI");

            // Proveri da li su podaci vezani za odeljak 'aic'
            if (isset($args['data']['aic']) && is_array($args['data']['aic'])) {
                $aicData = $args['data']['aic'];
                         error_log("VANJSKI");
                // Sačuvaj stil
                if (isset($aicData['style'])) {
                    $savedOptions['style'] = $aicData['style'];
                    error_log("MOJ SACUVANI STIL");
                    error_log(print_r($aicData['style'], true));
                }

                // Sačuvaj kreativnost
                if (isset($aicData['creativity'])) {
                    $savedOptions['creativity'] = $aicData['creativity'];
                }

                // Sačuvaj dužinu
                if (isset($aicData['length'])) {
                    $savedOptions['length'] = $aicData['length'];
                }

                // Sačuvaj jezik
                if (isset($aicData['language'])) {
                    $savedOptions['language'] = $aicData['language'];
                }
                error_log("OVDJE SAVED OPTIONS - NOVI");
                error_log(print_r($savedOptions, true));

                // Ovde možeš dalje obrađivati $savedOptions, kao što je čuvanje u bazi podataka
                error_log('Saved options: ' . print_r($savedOptions, true));
            } else {
                error_log('No valid data found in args[data][aic]');
            }
        } else {
            error_log('args[data] is not set or not an array.');
        }


        return $args;
    }







}
