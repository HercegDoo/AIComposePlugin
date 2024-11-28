
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

  #generatemail(additionalData = null) {
    const requestData = getRequestDataFields();
    //Prethodni razgovor sa izvrsenom provjerom potpisa 
    const previousConversationObject = signatureCheckedPreviousConversation(requestData.previousGeneratedEmail);
    requestData.previousGeneratedEmail= getFormattedMail( `${getPreviousGeneratedInsertedEmail()}`);
    requestData.previousConversation = previousConversationObject.previousConversation;
    requestData.signaturePresent = previousConversationObject.signaturePresent;
    requestData.instructions = additionalData ? (additionalData.passedInstruction === "" ? requestData.instructions : additionalData.passedInstruction) : requestData.instructions;
    requestData.fixText  = additionalData ? additionalData.fixText : "";


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
          const additionalData = {
            passedInstruction : targeteredInstruction.message,
            fixText: ""
          }
          return rcmail.command('generatemail', additionalData);
         }
      }
    })
  }

  #connectHelpExamplesWithCommand(){
    const helpATags = document.getElementsByClassName('help-a');
    Array.from(helpATags).forEach((helpATag)=>{
      helpATag.onclick  = function(){ document.getElementById('aic-compose-help-modal-mask').setAttribute('hidden', 'hidden');
        rcmail.enable_command('generatemail', true);
        const additionalData = {
          passedInstruction : helpATag.previousElementSibling.textContent,
          fixText: ""
        }
        return rcmail.command('generatemail', additionalData);}

    })
  }

  #connectFixTextWithCommand(){
    const fixTextSendButton = document.getElementById('fix-text-send');

    fixTextSendButton.style.cursor = "default";
  }

}

