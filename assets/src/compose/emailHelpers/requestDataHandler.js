import { getRecipientInfo } from "./recipientDataHandler";
import { getSenderInfo, processSenderData } from "./senderDataHandler";
import { getSubject } from "./subjectHandler";
import { signatureCheckedPreviousConversation } from "./signaturesHandler";

export function getRequestDataFields() {
  const styleElement = document.getElementById("aic_style_select"),
   lengthElement = document.getElementById("aic_length_select"),
   creativityElement = document.getElementById("aic_creativity_select"),
   languageElement = document.getElementById("aic_language_select"),
   senderInfo = processSenderData(getSenderInfo()),
    instructions = document.getElementById('aic-instruction');

  return {
    style: `${styleElement?.value || rcmail.env.aiPluginOptions.defaultStyle}`,
    senderName: `${senderInfo?.senderName || "" }`,
    recipientName: `${ getRecipientInfo().recipientName || ""}`,
    length: `${lengthElement?.value || rcmail.env.aiPluginOptions.defaultLength}`,
    creativity: `${creativityElement?.value || rcmail.env.aiPluginOptions.defaultCreativity}`,
    language: `${languageElement?.value || rcmail.env.aiPluginOptions.defaultLanguage}`,
    subject: `${getSubject()}`,
    recipientEmail: `${getRecipientInfo().recipientEmail}`,
    senderEmail: `${senderInfo.senderEmail}`,
    instructions: `${instructions.value}`,
    previousConversation: "",
    fixText: "",
    previousGeneratedEmailText: "",
  };
}
