import { getPreviousConversation } from "./getPreviousConversation";
import { getRecipientInfo } from "./recipientDataHandler";
import { getSenderInfo, processSenderData } from "./senderDataHandler";
import { fieldsValid } from "./fieldsValidation";
import { getSubject } from "./subjectHandler";

export function sendRequestData() {
  const generateEmailButton = document.getElementById("generate-email-button");
  const senderNameElement = document.getElementById("sender-name");
  const recipientNameElement = document.getElementById("recipient-name");
  const styleElement = document.getElementById("aic-style");
  const lengthElement = document.getElementById("aic-length");
  const creativityElement = document.getElementById("aic-creativity");
  const languageElement = document.getElementById("aic-language");
  const instructionsElement = document.getElementById("aic-instructions");
  const previousConversation = getPreviousConversation();
  const recipientInfo = getRecipientInfo();
  const senderInfo = processSenderData(getSenderInfo());
  const subject = getSubject();
  const textarea = document.getElementById("aic-email");

  generateEmailButton.addEventListener("click", () => {
    if (fieldsValid()) {
      const lock = rcmail.set_busy(true, "Genrisanje");
      generateEmailButton.setAttribute("disabled", "disabled");
      rcmail
        .http_post(
          "plugin.AIComposePlugin_GenereteEmailAction",
          {
            senderName: `${senderNameElement.value}`,
            recipientName: `${recipientNameElement.value}`,
            instructions: `${instructionsElement.value}`,
            style: `${styleElement.value}`,
            length: `${lengthElement.value}`,
            creativity: `${creativityElement.value}`,
            language: `${languageElement.value}`,
            previousConversation: `${previousConversation}`,
            recipientEmail: `${recipientInfo.recipientEmail}`,
            senderEmail: `${senderInfo.senderEmail}`,
            subject: `${subject}`,
          },
          lock
        )
        .done(function (data) {
          textarea.value =
            data && data["test"] !== undefined ? data["test"] : "";
        })
        .always(function (data) {
          rcmail.set_busy(false, "", lock);
          generateEmailButton.removeAttribute("disabled");
        });
    }
  });
}
