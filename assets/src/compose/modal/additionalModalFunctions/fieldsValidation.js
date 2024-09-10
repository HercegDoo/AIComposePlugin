import 'parsleyjs'; // Importuje Parsley
import $ from 'jquery';
import {translation} from "../../../utils"; // Importuje jQuery

export function validateFields() {
    $('#generate-email-button').on('click', function(event) {
        event.preventDefault(); // Spriječi defaultno ponašanje dugmeta

        // Validacija textarea i drugih elemenata
        let $textarea = $('#aic-instructions');
        let $senderName = $('#sender-name');
        let $recipientName = $('#recipient-name');

        $textarea.attr('data-parsley-required-message',translation('ai_error_instructions'));
        $senderName.attr('data-parsley-required-message', translation('ai_error_sender_name'));
        $recipientName.attr('data-parsley-required-message', translation('ai_error_recipient_name'));
        // Pokreni Parsley validaciju za sve elemente
        $textarea.parsley().validate();
        $senderName.parsley().validate();
        $recipientName.parsley().validate();


        // Prikazivanje poruka grešaka ispod svakog nevalidnog elementa
        if (!$textarea.parsley().isValid()) {
            $('#aic-instructions-error').text($textarea.attr('data-parsley-required-message'));
        } else {
            $('#aic-instructions-error').text(''); // Očisti poruku ako je sve u redu
        }

        if (!$senderName.parsley().isValid()) {
            $('#sender-name-error').text($senderName.attr('data-parsley-required-message'));
        } else {
            $('#sender-name-error').text(''); // Očisti poruku ako je sve u redu
        }

        if (!$recipientName.parsley().isValid()) {
            $('#recipient-name-error').text($recipientName.attr('data-parsley-required-message'));
        } else {
            $('#recipient-name-error').text(''); // Očisti poruku ako je sve u redu
        }
    });
}
