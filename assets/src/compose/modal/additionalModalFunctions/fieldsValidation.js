import 'parsleyjs'; // Importuje Parsley
import $ from 'jquery';
import {translation} from "../../../utils";

export function validateFields() {
    $('#generate-email-button').on('click', function(event) {
        event.preventDefault();


        let $textarea = $('#aic-instructions');
        let $senderName = $('#sender-name');
        let $recipientName = $('#recipient-name');
        //Lokalizacija za poruke
        $textarea.attr('data-parsley-required-message',translation('ai_error_instructions'));
        $senderName.attr('data-parsley-required-message', translation('ai_error_sender_name'));
        $recipientName.attr('data-parsley-required-message', translation('ai_error_recipient_name'));

        $textarea.parsley().validate();
        $senderName.parsley().validate();
        $recipientName.parsley().validate();



        if (!$textarea.parsley().isValid()) {
            $('#aic-instructions-error').text($textarea.attr('data-parsley-required-message'));
        } else {
            $('#aic-instructions-error').text('');
        }

        if (!$senderName.parsley().isValid()) {
            $('#sender-name-error').text($senderName.attr('data-parsley-required-message'));
        } else {
            $('#sender-name-error').text('');
        }

        if (!$recipientName.parsley().isValid()) {
            $('#recipient-name-error').text($recipientName.attr('data-parsley-required-message'));
        } else {
            $('#recipient-name-error').text('');
        }
    });
}

export function fieldsValid() {
const senderNameElement = document.getElementById('sender-name');
const instructionsElement = document.getElementById('aic-instructions');

    return senderNameElement.value !== ""  && instructionsElement.value !== "";
}

