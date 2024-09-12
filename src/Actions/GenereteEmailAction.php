<?php

namespace HercegDoo\AIComposePlugin\Actions;

use HercegDoo\AIComposePlugin\Actions\AbstractAction;
use HercegDoo\AIComposePlugin\AIEmailService\Entity\RequestData;
use rcube_utils;


final class GenereteEmailAction extends AbstractAction
{

    private RequestData $aiRequestData;

    public function handler(): void
    {
        $this->preparePostData();



        // ai generate email


        //naci nacin da radis return u output


        $this->rcmail->output->send([
            'testporuka' => time()
        ]);
    }

    private function preparePostData(): void
    {


        $senderName = rcube_utils::get_input_value('senderName', rcube_utils::INPUT_POST);;
        $recipientName = rcube_utils::get_input_value('recipientName', rcube_utils::INPUT_POST);
        $instructions = rcube_utils::get_input_value('instructions', rcube_utils::INPUT_POST);
        $style = rcube_utils::get_input_value('style', rcube_utils::INPUT_POST);
        $length = rcube_utils::get_input_value('length', rcube_utils::INPUT_POST);
        $creativity = rcube_utils::get_input_value('creativity', rcube_utils::INPUT_POST);
        $language = rcube_utils::get_input_value('language', rcube_utils::INPUT_POST);
        $previousConversation = rcube_utils::get_input_value('previousConversation', rcube_utils::INPUT_POST);
        $recipientEmail = rcube_utils::get_input_value('recipientEmail', rcube_utils::INPUT_POST);
        $senderEmail = rcube_utils::get_input_value('senderEmail', rcube_utils::INPUT_POST);
        $subject = rcube_utils::get_input_value('subject', rcube_utils::INPUT_POST);

        $this->aiRequestData = RequestData::make($recipientName, $senderName, $instructions, $style, $length, $creativity, $language);
        $this->aiRequestData->setSenderEmail($senderEmail)->setRecipientEmail($recipientEmail)->setSubject($subject);



        // if send
    }

    protected function validate(): void
    {
        $senderName = rcube_utils::get_input_value('senderName', rcube_utils::INPUT_POST);
        if (empty($senderName)) {
            $this->setError("Polje sender name obavezno");
        }

        if (strlen($senderName) < 3) {
            $this->setError("Polje sender name mora biti minimlano 3 karaktera");
        }

    }
}