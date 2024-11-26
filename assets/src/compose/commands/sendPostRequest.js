
import { getRequestDataFields } from "../modal/additionalModalFunctions/requestDataHandler";
import { insertEmail } from "../modal/additionalModalFunctions/insertEmailHandler";

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

    rcmail.lock_frame(document.body);
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

