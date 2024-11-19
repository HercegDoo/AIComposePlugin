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

        $attrib = ['name' => $name];

        $selector = new \html_select($attrib);

        foreach ($options as $option) {
            $capitalizedValue = ucfirst($option);
            $selector->add($capitalizedValue, $option);
        }

        $sel = $defaultOption;

        return $selector->show($sel);
    }

    public function createInstructionField($attrib){
        $attrisb = [
            'name'  => 'aicinstruction',
            'id'    => 'aic-instruction',
            'rows'  => 1,
            'cols'  => 50,
            'class' => 'form-control'
        ];
        $textarea = new \html_textarea($attrisb);
        return $textarea->show('');
    }

    public function fillPredefinedInstructions(){
        $html = new \html();
        $liTagsContainer = "";
        $predefinedInstructions = \rcmail::get_instance()->output->get_env('aiPredefinedInstructions');
        foreach($predefinedInstructions as $predefinedInstruction) {
          $spanTag = $html::span([], $predefinedInstruction['title']);
          $aTag = $html::tag('a', ['role' => 'button', 'class' => 'recipient active', 'tabindex' => -1], $spanTag);
          $liTag = $html::tag('li', ['class' => 'menuitem'], $aTag);
          $liTagsContainer .= $liTag;
        }
        return $liTagsContainer;
    }
}
