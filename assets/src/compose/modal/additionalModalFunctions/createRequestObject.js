import {getPreviousConversation} from "./getPreviousConversation";
import {getRecipientInfo} from "./recipientDataHandler";
import {getSenderInfo, processSenderData} from "./senderDataHandler";
import {fieldsValid} from "./fieldsValidation";
import {getSubject} from "./subjectHandler";


export function showRequestData() {
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

  generateEmailButton.addEventListener("click", () => {

    if(fieldsValid()){
      const requestData = {
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
        subject: `${subject}`
      };
      console.log(requestData);
    }

  });
}
