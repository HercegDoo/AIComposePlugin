<?php

namespace HercegDoo\AIComposePlugin\Actions\Settings;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;

class AddInstruction extends AbstractAction
{
    protected static $response;

    public static function response_form($attrib)
    {
        error_log('Uso u response form');
        $rcmail = \rcmail::get_instance();
        error_log('Uso u response_form');

        // add some labels to client
        $rcmail->output->add_label('converting', 'editorwarning');

        // Set form tags and hidden fields
        $readonly = !empty(self::$response['static']);
        $id = self::$response['id'] ?? '';
        $hidden = ['name' => '_id', 'value' => $id];

        // Ručno postavljanje form početka i kraja
        $form_start = '<form method="post" action="">'; // Akcija prazna, forma se šalje na trenutni URL
        $form_end = '</form>';

        // Ostatak koda za prikaz forme
        $name_attr = [
            'id' => 'ffname',
            'size' => $attrib['size'] ?? null,
            'readonly' => $readonly,
            'required' => true,
        ];

        // Atributi za textarea
        $text_attr = [
            'id' => 'fftext',
            'size' => $attrib['textareacols'] ?? null,
            'rows' => $attrib['textarearows'] ?? null,
            'readonly' => $readonly,
            'spellcheck' => true,
        ];
        $rcmail->output->command('konj');
        // Kreiranje tabele
        $table = new \html_table(['cols' => 1]);

        // Dodavanje imena
        $table->add(null, \html::label('ffname', \rcube::Q($rcmail->gettext('responsename'))));
        $table->add(null, \rcube_output::get_edit_field('name', self::$response['name'] ?? '', $name_attr, 'text'));

        // Dodavanje textarea
        $table->add(null, \html::label('fftext', \rcube::Q($rcmail->gettext('responsetext'))));
        $table->add(null, \rcube_output::get_edit_field('text', self::$response['data'] ?? '', $text_attr, 'textarea'));

        // Vraćanje kompletne forme kao tabela
        return "{$form_start}\n" . $table->show($attrib) . $form_end;
    }





    protected function handler($args = []): void
    {
        $rcmail = \rcmail::get_instance();
        $title = $rcmail->gettext($rcmail->action == 'add-response' ? 'addresponse' : 'editresponse');

        if (!empty($args['post'])) {
            self::$response = $args['post'];
        } elseif ($id = \rcube_utils::get_input_string('_id', \rcube_utils::INPUT_GP)) {
            self::$response = $rcmail->get_compose_response($id);
            if (!\is_array(self::$response)) {
                $rcmail->output->show_message('dberror', 'error');
                $rcmail->output->send('iframe');
            }
        }

        $rcmail->output->set_pagetitle($title);
        error_log('Posslije set page title');
        $rcmail->output->set_env('readonly', !empty(self::$response['static']));
        $rcmail->output->add_handler('responseform', [$this, 'response_form']);
        $rcmail->output->add_handler('submitbtn', [$this, 'submit_handler']);
        $rcmail->output->show_message('Test message from handler', 'confirmation');

        $rcmail->output->send('AIComposePlugin.instructionedit');
    }

    //
    //    protected function validate(): void
    //    {
    //       error_log("nista");
    //    }
    protected function validate(): void
    {
        // TODO: Implement validate() method.
    }
}
