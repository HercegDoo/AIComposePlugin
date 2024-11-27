
import { getRequestDataFields } from "../modal/additionalModalFunctions/requestDataHandler";
import { getPreviousGeneratedInsertedEmail, insertEmail } from "../modal/additionalModalFunctions/insertEmailHandler";
import { signatureCheckedPreviousConversation } from "../modal/additionalModalFunctions/signaturesHandler";
import { getFormattedMail } from "../../utils";
import { display_messages, errorPresent, validateFields } from "../validateFields";

export default class GenerateMail {

  constructor() {
    this.predefinedInstructions = document.querySelector('#predefined-instructions-dropdown');
    this.#registerCommands();
  }

  #registerCommands() {
    rcube_webmail.prototype.generatemail = this.#generatemail;

    rcmail.register_command('generatemail');

    this.#connectPredefinedInstructionsWithCommand();
    this.#connectHelpExamplesWithCommand();

  }

  #generatemail(passedInstruction = "", fixText = "") {
    const requestData = getRequestDataFields();
    //Prethodni razgovor sa izvrsenom provjerom potpisa 
    const previousConversationObject = signatureCheckedPreviousConversation(requestData.previousGeneratedEmail);
    requestData.previousGeneratedEmail= getFormattedMail( `${getPreviousGeneratedInsertedEmail()}`);
    requestData.previousConversation = previousConversationObject.previousConversation;
    requestData.signaturePresent = previousConversationObject.signaturePresent;
    requestData.instructions = passedInstruction === "" ? requestData.instructions : passedInstruction;
    requestData.fixText  = fixText;

    const errorsArray = validateFields();
    if(errorsArray.length !== 0){
      display_messages(errorsArray);
    }

    if(errorPresent(errorsArray)){
     return;
    }

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
        const instructionTextArea = document.getElementById('aic-instruction');
        //Ako nema nista u instrukciji, ubaci datu instrukciju(za slucaj koristenja predefinisane instrukcije)
        instructionTextArea.textContent = requestData.instructions;
      })
      .always(function() {
        rcmail.unlock_frame();
      });
  }


  #connectPredefinedInstructionsWithCommand(){
    const predefinedInstructionsChildrenArray = Array.from(this.predefinedInstructions.children);
    predefinedInstructionsChildrenArray.forEach((predefinedInstruction)=>{
      if(!predefinedInstruction.hasAttribute('role')){
        const targeteredInstruction =rcmail.env.aiPredefinedInstructions.find(originalPredefinedInstruction => originalPredefinedInstruction.id === predefinedInstruction.id.replace('dropdown-', ""));
        predefinedInstruction.onclick  = function(){ rcmail.enable_command('generatemail', true);
          return rcmail.command('generatemail', targeteredInstruction.message);
         }
      }
    })
  }

  #connectHelpExamplesWithCommand(){
    const helpATags = document.getElementsByClassName('help-a');
    Array.from(helpATags).forEach((helpATag)=>{
      helpATag.onclick  = function(){ document.getElementById('aic-compose-help-modal-mask').setAttribute('hidden', 'hidden');
        rcmail.enable_command('generatemail', true);
        return rcmail.command('generatemail', helpATag.previousElementSibling.textContent);}

    })
  }

}

