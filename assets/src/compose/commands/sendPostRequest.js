
import { getRequestDataFields } from "../modal/additionalModalFunctions/requestDataHandler";
import { insertEmail } from "../modal/additionalModalFunctions/insertEmailHandler";
import { signatureCheckedPreviousConversation } from "../modal/additionalModalFunctions/signaturesHandler";

export default class GenerateMail {

  constructor() {
    this.#registerCommands();
  }

  #registerCommands() {
    rcube_webmail.prototype.generatemail = this.#generatemail;

    rcmail.enable_command('generatemail', true);
    rcmail.register_command('generatemail');


  }

  #generatemail() {
    const requestData = getRequestDataFields();
    const previousConversationObject = signatureCheckedPreviousConversation(requestData.previousGeneratedEmail);
    requestData.previousConversation = previousConversationObject.previousConversation;
    requestData.signaturePresent = previousConversationObject.signaturePresent;
    rcmail.lock_frame(document.body);
    console.log(requestData);
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
      .done(function(data){
        insertEmail(data && data["respond"] !== undefined ? data["respond"] : "");
      })
      .always(function() {
        rcmail.unlock_frame();
      });
  }
}

