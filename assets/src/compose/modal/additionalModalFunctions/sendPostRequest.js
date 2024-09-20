import { fieldsValid } from "./fieldsValidation";
import { getSelectedText } from "./selectedTextHandler";
import { getRequestDataFields } from "./requestDataHandler";

export function sendPostRequest(previousGeneratedEmail = "", instructionsElement =  document.getElementById("aic-instructions"), ){
  const generateEmailButton = document.getElementById("generate-email-button");
  const textarea = document.getElementById("aic-email");
  const generateEmailSpan = document.getElementById('generate-email-span');
  const generateAgainSpan = document.getElementById('generate-again-span');
  const insertEmailButton = document.getElementById('insert-email-button');

  let requestData = getRequestDataFields();
  requestData = {
    ...requestData,
    previousGeneratedEmail: `${previousGeneratedEmail}`,
    instructions: `${instructionsElement.value}`,
    fixText: `${getSelectedText()}`
  }

  if (fieldsValid()) {
    console.log(requestData);
    const lock = rcmail.set_busy(true, "Genrisanje");
    generateEmailButton.setAttribute("disabled", "disabled");
    if(!insertEmailButton.hasAttribute('hidden')){
      insertEmailButton.setAttribute('disabled', 'disabled');
    }
    rcmail
      .http_post(
        "plugin.AIComposePlugin_GenereteEmailAction",
        {
          senderName: `${requestData.senderName}`,
          recipientName: `${requestData.recipientName}`,
          instructions: `${requestData.instructions}`,
          style: `${requestData.style}`,
          length: `${requestData.length}`,
          creativity: `${requestData.creativity}`,
          language: `${requestData.language}`,
          previousConversation: `${requestData.previousConversation}`,
          previousGeneratedEmailText: `${requestData.previousGeneratedEmail}`,
         fixText: `${requestData.fixText}`,
          recipientEmail: `${requestData.recipientEmail}`,
          senderEmail: `${requestData.senderEmail}`,
          subject: `${requestData.subject}`,
        },
        lock
      )
      .done(function (data) {
        textarea.value =
          data && data["respond"] !== undefined ? data["respond"] : "";
        generateEmailSpan.style.display = 'none';
        generateAgainSpan.style.display = 'block';
        insertEmailButton.removeAttribute('hidden');
        insertEmailButton.removeAttribute('disabled');
      })
      .always(function (data) {
        rcmail.set_busy(false, "", lock);
        generateEmailButton.removeAttribute("disabled");
      });
  }
}