import { getRecipientInfo } from "./recipientDataHandler";
import { getSenderInfo, processSenderData } from "./senderDataHandler";
import { getSubject } from "./subjectHandler";
import { signatureCheckedPreviousConversation } from "./signaturesHandler";

export function getRequestDataFields() {
  const styleElement = document.getElementById("aic-style");
  const senderNameElement = document.getElementById("sender-name");
  const recipientNameElement = document.getElementById("recipient-name");
  const lengthElement = document.getElementById("aic-length");
  const creativityElement = document.getElementById("aic-creativity");
  const languageElement = document.getElementById("aic-language");
  const senderInfo = processSenderData(getSenderInfo());
  const signatureCheckObject = signatureCheckedPreviousConversation();

  return {
    style: `${styleElement?.value || rcmail.env.aiPluginOptions.defaultStyle}`,
    senderName: `${senderNameElement?.value || senderInfo.senderName }`,
    recipientName: `${recipientNameElement?.value || getRecipientInfo().recipientName}`,
    length: `${lengthElement?.value || rcmail.env.aiPluginOptions.defaultLength}`,
    creativity: `${creativityElement?.value || rcmail.env.aiPluginOptions.defaultCreativity}`,
    language: `${languageElement?.value || rcmail.env.aiPluginOptions.defaultLanguage}`,
    previousConversation: `${signatureCheckObject.previousConversation}`,
    signaturePresent: `${signatureCheckObject.signaturePresent}`,
    subject: `${getSubject()}`,
    recipientEmail: `${getRecipientInfo().recipientEmail}`,
    senderEmail: `${senderInfo.senderEmail}`,
  };
}
