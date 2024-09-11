import {getPreviousConversation} from "./getPreviousConversation";
import { getSelectedText } from "./checkSelectedText";
import {getRecipientInfo} from "./getRecipientInfo";
import {getSenderInfo, processSenderData} from "./senderDataHandler";


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
  const previousGeneratedEmailElement = document.getElementById("aic-email");
  const recipientInfo = getRecipientInfo();
  const senderInfo = processSenderData(getSenderInfo());

  generateEmailButton.addEventListener("click", () => {
    const textForFixing = getSelectedText();

    const requestData = {
      senderName: `${senderNameElement.value}`,
      recipientName: `${recipientNameElement.value}`,
      instructions: `${instructionsElement.value}`,
      style: `${styleElement.value}`,
      length: `${lengthElement.value}`,
      creativity: `${creativityElement.value}`,
      language: `${languageElement.value}`,
      previousConversation: `${previousConversation}`,
      previousGeneratedEmail: `${previousGeneratedEmailElement.value}`,
      textForFixing: `${textForFixing}`,
      recipientEmail: `${recipientInfo.recipientEmail}`,
      senderEmail: `${senderInfo.senderEmail}`
    };
    console.log(requestData);
  });
}
