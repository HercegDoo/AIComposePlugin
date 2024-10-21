import { fieldsValid } from "../fieldsValidation";
import { getRequestDataFields } from "../requestDataHandler";
import { getPreviousGeneratedInsertedEmail, insertEmail } from "../insertEmailHandler";
import { signatureCheckedPreviousConversation } from "../signaturesHandler";

export function sendPostRequest(
  previousGeneratedEmail = "",
  instructionsElementValue = document.getElementById("aic-instructions").value, fixText = ""
) {
  const generateEmailButton = document.getElementById("generate-email-button");
  const textarea = document.getElementById("aic-email");
  const generateEmailSpan = document.getElementById("generate-email-span");
  const generateAgainSpan = document.getElementById("generate-again-span");
  const insertEmailButton = document.getElementById("insert-email-button");
  const aiComposeModal = document.getElementById("aic-compose-dialog");

  let requestData = getRequestDataFields();
  requestData = {
    ...requestData,
    previousGeneratedEmail: `${previousGeneratedEmail !== "" ? previousGeneratedEmail : getPreviousGeneratedInsertedEmail()}`,
    instructions: `${instructionsElementValue}`,
    fixText: `${fixText}`,
  };


    //Uzimanje prethodnog razgovora bez prethodno generisanog maila
  const previousConversationObject = signatureCheckedPreviousConversation(requestData.previousGeneratedEmail);
    requestData.previousConversation = previousConversationObject.previousConversation;
    requestData.signaturePresent = previousConversationObject.signaturePresent;


  if (fieldsValid()) {
    rcmail.lock_frame(document.body);
    if(aiComposeModal){
      generateEmailButton.setAttribute("disabled", "disabled");
      if (!insertEmailButton.hasAttribute("hidden")) {
        insertEmailButton.setAttribute("disabled", "disabled");
      }
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
          signaturePresent: `${requestData.signaturePresent}`,
          previousGeneratedEmailText: `${requestData.previousGeneratedEmail}`,
          fixText: `${requestData.fixText}`,
          recipientEmail: `${requestData.recipientEmail}`,
          senderEmail: `${requestData.senderEmail}`,
          subject: `${requestData.subject}`,
        },
        true
      )
      .done(function (data) {
        //Ako se generisanje maila desava u modalu
        if(aiComposeModal){
          textarea.value =
            data && data["respond"] !== undefined ? data["respond"] : "";
          generateEmailSpan.style.display = "none";
          generateAgainSpan.style.display = "block";
          insertEmailButton.removeAttribute("hidden");
          insertEmailButton.removeAttribute("disabled");
        }
        //Ako se generisanje maila desava van modala (Dropdown predefinisane instrukcije)
        else
          insertEmail(data && data["respond"] !== undefined ? data["respond"] : "");
      })
      .always(function () {
        rcmail.unlock_frame();
       aiComposeModal && generateEmailButton.removeAttribute("disabled");
      });
  }
}
