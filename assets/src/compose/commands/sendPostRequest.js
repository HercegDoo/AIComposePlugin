
import { getRequestDataFields } from "../modal/additionalModalFunctions/requestDataHandler";
import { getPreviousGeneratedInsertedEmail, insertEmail } from "../modal/additionalModalFunctions/insertEmailHandler";
import { signatureCheckedPreviousConversation } from "../modal/additionalModalFunctions/signaturesHandler";
import { getFormattedMail } from "../../utils";

export default class GenerateMail {

  constructor() {
    this.predefinedInstructions = null;
    this.#registerCommands();
  }

  #registerCommands() {
    rcube_webmail.prototype.generatemail = this.#generatemail;

    rcmail.enable_command('generatemail', true);
    rcmail.register_command('generatemail');

    this.#connectPredefinedInstructionsWithCommand();

  }

  #generatemail(passedInstruction = "") {
    const requestData = getRequestDataFields();
    //Prethodni razgovor sa izvrsenom provjerom potpisa 
    const previousConversationObject = signatureCheckedPreviousConversation(requestData.previousGeneratedEmail);
    requestData.previousGeneratedEmail= getFormattedMail( `${getPreviousGeneratedInsertedEmail()}`);
    requestData.previousConversation = previousConversationObject.previousConversation;
    requestData.signaturePresent = previousConversationObject.signaturePresent;
    requestData.instructions = passedInstruction === "" ? requestData.instructions : passedInstruction;
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
        const instructionTextArea = document.getElementById('aic-instruction');
        //Ako nema nista u instrukciji, ubaci datu instrukciju(za slucaj koristenja predefinisane instrukcije)
        instructionTextArea.textContent = instructionTextArea.textContent === "" ? passedInstruction : instructionTextArea.textContent;
      })
      .always(function() {
        rcmail.unlock_frame();
      });
  }


  #connectPredefinedInstructionsWithCommand(){
    this.predefinedInstructions = document.querySelector('#predefined-instructions-dropdown');
    const predefinedInstructionsChildrenArray = Array.from(this.predefinedInstructions.children);
    predefinedInstructionsChildrenArray.forEach((predefinedInstruction)=>{
      if(!predefinedInstruction.hasAttribute('role')){
        const targeteredInstruction =rcmail.env.aiPredefinedInstructions.find(originalPredefinedInstruction => originalPredefinedInstruction.id === predefinedInstruction.id.replace('dropdown-', ""));
      console.log(targeteredInstruction);
        predefinedInstruction.onclick  = function(){ return rcmail.command('generatemail', targeteredInstruction.message);}
      }
    })
  }

}

