<?php

namespace HercegDoo\AIComposePlugin\Actions\Settings;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;
use HercegDoo\AIComposePlugin\Actions\SkipValidationInterface;

class AddInstruction extends AbstractAction implements SkipValidationInterface
{
    protected static string $instructionId;

    /**
     * @param array<string, string> $attrib
     */
    public function response_form(array $attrib): string
    {
        $rcmail = \rcmail::get_instance();

        $predefinedInstructions = $rcmail->user->get_prefs()['predefinedInstructions'] ?? [];

        $title = '';
        $content = '';
        if (self::$instructionId) {
            foreach ($predefinedInstructions as $predefinedInstruction) {
                if (str_contains(self::$instructionId, $predefinedInstruction['id'])) {
                    $title = $predefinedInstruction['title'];
                    $content = $predefinedInstruction['message'];
                    break;
                }
            }
        }

        $rcmail->output->add_label('converting', 'editorwarning');

        [$form_start, $form_end] = \rcmail_action::get_form_tags($attrib, 'plugin.AIComposePlugin_SaveInstruction', self::$instructionId, ['name' => '_id', 'value' => self::$instructionId]);
        unset($attrib['form'], $attrib['id']);

        $name_attr = [
            'id' => 'ffname',
            'size' => $attrib['size'] ?? null,
            'required' => true,
        ];

        $text_attr = [
            'id' => 'fftext',
            'size' => $attrib['textareacols'] ?? null,
            'rows' => $attrib['textarearows'] ?? null,
            'spellcheck' => true,
        ];

        $table = new \html_table(['cols' => 1]); // Postavi samo jedan kolonu

        $table->add([], \html::label('ffname', \rcube::Q($this->translation('ai_predefined_title'))));
        $table->add([], \rcube_output::get_edit_field('name', $title, $name_attr, 'text'));

        $table->add([], \html::label('fftext', \rcube::Q($this->translation('ai_predefined_content'))));
        $table->add([], \rcube_output::get_edit_field('text', $content, $text_attr, 'textarea'));

        return "{$form_start}\n" . $table->show($attrib) . $form_end;
    }

    /**
     * @param array<string, string> $args
     */
    protected function handler(array $args = []): void
    {
        self::$instructionId = \rcube_utils::get_input_string('_id', \rcube_utils::INPUT_GET);
        $this->rcmail->output->add_handler('responseform', [$this, 'response_form']);
        $this->rcmail->output->send('AIComposePlugin.instructionedit');
    }

    protected function validate(): void
    {
    }
}
