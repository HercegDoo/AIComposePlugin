import { fieldsValid } from "./fieldsValidation";
import { getSelectedText } from "./checkSelectedText";
import { getRequestDataFields } from "./requestDataHandler";

export function sendPostRequest(previousGeneratedEmail = "", instructionsElement =  document.getElementById("aic-instructions"), ){
  const generateEmailButton = document.getElementById("generate-email-button");
  const textarea = document.getElementById("aic-email");

  let requestData = getRequestDataFields();
  requestData = {
    ...requestData,
    previousGeneratedEmail: `${previousGeneratedEmail}`,
    instructions: `${instructionsElement.value}`,
    fixText: `${getSelectedText()}`
  }

  console.log(requestData);

  if (fieldsValid()) {
    const lock = rcmail.set_busy(true, "Genrisanje");
    generateEmailButton.setAttribute("disabled", "disabled");
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
      })
      .always(function (data) {
        rcmail.set_busy(false, "", lock);
        generateEmailButton.removeAttribute("disabled");
      });
  }
}