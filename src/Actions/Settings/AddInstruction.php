<?php

namespace HercegDoo\AIComposePlugin\Actions\Settings;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;

class AddInstruction extends AbstractAction
{
    protected static $instructionId;

    public static function response_form($attrib)
    {
        error_log('SAtributi za addInstruction: ' . print_r($attrib, true));
        error_log('Uso u handler addinstruction');
        $rcmail = \rcmail::get_instance();

        $predefinedInstructions = $rcmail->user->get_prefs()['predefinedInstructions'] ?? [];
        //        error_log("Predefinisane instrukcije: " . print_r($predefinedInstructions, true));
        $title = '';
        $content = '';
        if (self::$instructionId) {
            foreach ($predefinedInstructions as $predefinedInstruction) {
                error_log('id predefinisane instrukcije' . print_r($predefinedInstruction, true));
                error_log('id dobijeni' . print_r(self::$instructionId, true));
                if ($predefinedInstruction['id'] === self::$instructionId) {
                    $title = $predefinedInstruction['title'];
                    $content = $predefinedInstruction['message'];
                    break;
                }
            }
        }

        $rcmail->output->add_label('converting', 'editorwarning');

        $readonly = !empty(self::$response['static']);
        $id = self::$response['id'] ?? '';
        $hidden = ['name' => '_id', 'value' => $id];

        [$form_start, $form_end] = \rcmail_action::get_form_tags($attrib, 'plugin.AIComposePlugin_SaveInstruction', $id, $hidden);
        unset($attrib['form'], $attrib['id']);

        $name_attr = [
            'id' => 'ffname',
            'size' => $attrib['size'] ?? null,
            //            'readonly' => $readonly,
            'required' => true,
        ];

        $text_attr = [
            'id' => 'fftext',
            'size' => $attrib['textareacols'] ?? null,
            'rows' => $attrib['textarearows'] ?? null,
            //            'readonly' => $readonly,
            'spellcheck' => true,
        ];

        $table = new \html_table(['cols' => 1]); // Postavi samo jedan kolonu

        $table->add(null, \html::label('ffname', \rcube::Q($rcmail->gettext('responsename'))));
        $table->add(null, \rcube_output::get_edit_field('name', $title, $name_attr, 'text'));

        $table->add(null, \html::label('fftext', \rcube::Q($rcmail->gettext('responsetext'))));
        $table->add(null, \rcube_output::get_edit_field('text', $content, $text_attr, 'textarea'));

        return "{$form_start}\n" . $table->show($attrib) . $form_end;
    }

    protected function handler($args = []): void
    {
        self::$instructionId = \rcube_utils::get_input_string('_id', \rcube_utils::INPUT_GET);
        error_log('Dobijeni ID: ' . print_r(self::$instructionId, true));

        error_log('Mjesto handler - args: ' . print_r($args, true));
        $rcmail = \rcmail::get_instance();
        $title = $rcmail->gettext($rcmail->action == 'add-response' ? 'addresponse' : 'editresponse');

        //        if (!empty($args['post'])) {
        //            self::$response = $args['post'];
        //        } elseif ($id = \rcube_utils::get_input_string('_id', \rcube_utils::INPUT_GP)) {
        //            self::$response = $rcmail->get_compose_response($id);
        //            if (!\is_array(self::$response)) {
        //                $rcmail->output->show_message('dberror', 'error');
        //                $rcmail->output->send('iframe');
        //            }
        //        }

        $rcmail->output->set_pagetitle($title);
        $rcmail->output->add_handler('responseform', [$this, 'response_form']);
        $rcmail->output->show_message('Test message from handler', 'confirmation');
        $rcmail->output->send('AIComposePlugin.instructionedit');
    }

    protected function validate(): void
    {
        // TODO: Implement validate() method.
    }
}
