<?php

namespace HercegDoo\AIComposePlugin\Utilities;

class TemplateObjectFiller
{
    private static ?TemplateObjectFiller $instance = null;

    private function __construct()
    {
    }

    public static function getTemplateObjectFiller(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function createSelectField(string $options_key, string $name): string
    {
        $defaultValue = 'default' . ucfirst($options_key);
        $defaultValue = substr($defaultValue, 0, -1);

        $options = [];
        $defaultOption = '';

        $aiPluginOptionsArray = \rcmail::get_instance()->output->get_env('aiPluginOptions');
        if (\is_array($aiPluginOptionsArray) && isset($aiPluginOptionsArray[$options_key])) {
            $options = $aiPluginOptionsArray[$options_key];
            $defaultOption = $aiPluginOptionsArray[$options_key === 'creativities' ? 'defaultCreativity' : $defaultValue];
        }

        $attrib = ['name' => $name];

        $selector = new \html_select($attrib);

        foreach ((array) $options as $option) {
            $capitalizedValue = \is_string($option) ? ucfirst($option) : $option;
            $selector->add($capitalizedValue, $option);
        }

        $sel = $defaultOption;

        return \is_string($sel) ? $selector->show($sel) : '';
    }

    public function createInstructionField(): string
    {
        $attrisb = [
            'name' => 'aicinstruction',
            'id' => 'aic-instruction',
            'rows' => 1,
            'cols' => 50,
            'class' => 'form-control',
        ];
        $textarea = new \html_textarea($attrisb);

        return $textarea->show('');
    }

    public function fillPredefinedInstructions(): string
    {
        $html = new \html();
        $liTagsContainer = '';
        $predefinedInstructions = \rcmail::get_instance()->output->get_env('aiPredefinedInstructions');
        foreach ((array) $predefinedInstructions as $predefinedInstruction) {
            if (\is_array($predefinedInstruction)) {
                $spanTag = $html::span([], \is_string($predefinedInstruction['title']) ? $predefinedInstruction['title'] : 'Error');
                $aTag = $html::tag('a', ['role' => 'button', 'class' => 'recipient active', 'tabindex' => -1], $spanTag);
                $liTag = $html::tag('li', ['class' => 'menuitem'], $aTag);
                $liTagsContainer .= $liTag;
            }
        }

        return $liTagsContainer;
    }
}
