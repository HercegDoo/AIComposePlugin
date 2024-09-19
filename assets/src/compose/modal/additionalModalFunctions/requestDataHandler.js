import { getPreviousConversation } from "./getPreviousConversation";
import { getRecipientInfo } from "./recipientDataHandler";
import { getSenderInfo, processSenderData } from "./senderDataHandler";
import { getSubject } from "./subjectHandler";

export function getRequestDataFields(){
  const styleElement = document.getElementById("aic-style");
  const senderNameElement = document.getElementById("sender-name");
  const recipientNameElement = document.getElementById("recipient-name");
  const lengthElement = document.getElementById("aic-length");
  const creativityElement = document.getElementById("aic-creativity");
  const languageElement = document.getElementById("aic-language");
  const senderInfo = processSenderData(getSenderInfo());

  return {
    style: `${styleElement.value}`,
    senderName: `${senderNameElement.value}`,
    recipientName: `${recipientNameElement.value}`,
    length: `${lengthElement.value}`,
    creativity: `${creativityElement.value}`,
    language: `${languageElement.value}`,
    previousConversation: `${getPreviousConversation()}`,
    subject: `${getSubject()}`,
    recipientEmail: `${getRecipientInfo().recipientEmail}`,
    senderEmail: `${senderInfo.senderEmail}`
  }
}