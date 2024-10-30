import "parsleyjs";
import $ from "jquery";
import { translation } from "../../../utils";

export function validateFields() {

    $("#fix-text-generate-again").on("click", function () {
      let $textarea = $("#fix-instructions");
      $textarea.attr(
        "data-parsley-required-message",
        translation("ai_error_instructions")
      );

      $textarea.parsley().validate();

      if (!$textarea.parsley().isValid()) {
        $("#aic-instructions-error").text(
          $textarea.attr("data-parsley-required-message")
        );
      } else {
        $("#aic-instructions-error").text("");
      }
    });

  $("#generate-email-button").on("click", function (event) {
    event.preventDefault();

    let $textarea = $("#aic-instructions");
    let $senderName = $("#sender-name");
    let $recipientName = $("#recipient-name");
    //Lokalizacija za poruke
    $textarea.attr(
      "data-parsley-required-message",
      translation("ai_error_instructions")
    );
    $senderName.attr(
      "data-parsley-required-message",
      translation("ai_error_sender_name")
    );
    $recipientName.attr(
      "data-parsley-required-message",
      translation("ai_error_recipient_name")
    );

    $textarea.parsley().validate();
    $senderName.parsley().validate();
    $recipientName.parsley().validate();

    if (!$textarea.parsley().isValid()) {
      $("#aic-instructions-error").text(
        $textarea.attr("data-parsley-required-message")
      );
    } else {
      $("#aic-instructions-error").text("");
    }

    if (!$senderName.parsley().isValid()) {
      $("#sender-name-error").text(
        $senderName.attr("data-parsley-required-message")
      );
    } else {
      $("#sender-name-error").text("");
    }

    if (!$recipientName.parsley().isValid()) {
      $("#recipient-name-error").text(
        $recipientName.attr("data-parsley-required-message")
      );
    } else {
      $("#recipient-name-error").text("");
    }
  });
}

export function fieldsValid() {
  const aiComposeModal = document.getElementById("aic-compose-dialog"),
   senderNameElement = document.getElementById("sender-name"),
   instructionsElement = document.getElementById("aic-instructions");

  return aiComposeModal ? senderNameElement.value !== "" && instructionsElement.value !== "" : true;
}

export function fixTextFieldsValid(){
  const fixTextTextarea = document.getElementById('fix-instructions');
  return fixTextTextarea.value !== "";
}
