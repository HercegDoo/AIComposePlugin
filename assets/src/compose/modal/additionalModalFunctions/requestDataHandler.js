import { getRecipientInfo } from "./recipientDataHandler";
import { getSenderInfo, processSenderData } from "./senderDataHandler";
import { getSubject } from "./subjectHandler";
import { signatureCheckedPreviousConversation } from "./signaturesHandler";

export function getRequestDataFields() {
  const styleElement = document.getElementById("aic-style"),
   senderNameElement = document.getElementById("sender-name"),
   recipientNameElement = document.getElementById("recipient-name"),
   lengthElement = document.getElementById("aic-length"),
   creativityElement = document.getElementById("aic-creativity"),
   languageElement = document.getElementById("aic-language"),
   senderInfo = processSenderData(getSenderInfo());

  return {
    style: `${styleElement?.value || rcmail.env.aiPluginOptions.defaultStyle}`,
    senderName: `${senderNameElement?.value || senderInfo.senderName }`,
    recipientName: `${recipientNameElement?.value || getRecipientInfo().recipientName}`,
    length: `${lengthElement?.value || rcmail.env.aiPluginOptions.defaultLength}`,
    creativity: `${creativityElement?.value || rcmail.env.aiPluginOptions.defaultCreativity}`,
    language: `${languageElement?.value || rcmail.env.aiPluginOptions.defaultLanguage}`,
    subject: `${getSubject()}`,
    recipientEmail: `${getRecipientInfo().recipientEmail}`,
    senderEmail: `${senderInfo.senderEmail}`,
  };
}
