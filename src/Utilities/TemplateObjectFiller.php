<?php

namespace HercegDoo\AIComposePlugin\Utilities;

class TemplateObjectFiller
{
    public function createSelectField($attrib, $options_key, $name): string
    {
        $defaultValue = 'default' . ucfirst($options_key);
        $defaultValue = substr($defaultValue, 0, -1);

        $options = \rcmail::get_instance()->output->get_env('aiPluginOptions')[$options_key];
        $defaultOption = \rcmail::get_instance()->output->get_env('aiPluginOptions')[$options_key === 'creativities' ? 'defaultCreativity' : $defaultValue];

        $capitalizedOptions = array_map('ucfirst', array_values($options));

        $attrib = ['name' => $name];

        $selector = new \html_select($attrib);

        foreach ($options as $key => $value) {
            $capitalizedValue = ucfirst($value);
            $selector->add($capitalizedValue, $value);
        }

        $sel = $defaultOption;

        return $selector->show($sel);
    }
}
