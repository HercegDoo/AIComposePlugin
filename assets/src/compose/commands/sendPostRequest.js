
import { getRequestDataFields } from "../emailHelpers/requestDataHandler";
import { getPreviousGeneratedInsertedEmail, insertEmail } from "../emailHelpers/insertEmailHandler";
import { signatureCheckedPreviousConversation } from "../emailHelpers/signaturesHandler";
import { getFormattedMail, translation } from "../../utils";
import { display_messages, errorPresent, validateFields } from "../emailHelpers/validateFields";

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
    document.getElementById('aic-generate-email-button').title = translation('ai_generate_email');

  }

  #generatemail(additionalData = null) {
    const requestData = getRequestDataFields();
    //Prethodni razgovor sa izvrsenom provjerom potpisa 
    requestData.previousGeneratedEmail= getFormattedMail( `${getPreviousGeneratedInsertedEmail()}`);
    const previousConversationObject = signatureCheckedPreviousConversation(requestData.previousGeneratedEmail);
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
        "plugin.AIComposePlugin_GenerateEmailAction",
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
          multipleRecipients: `${requestData.multipleRecipients}`
        },
        true
      )
      .done(function(data){
        insertEmail(data && data["respond"] !== undefined ? data["respond"] : "");
        const instructionTextArea = document.getElementById('aic-instruction');
        //Ako nema nista u instrukciji, ubaci datu instrukciju(za slucaj koristenja predefinisane instrukcije)
        if(additionalData === null){
          instructionTextArea.value = requestData.instructions;
        }
        else{
        instructionTextArea.value =  additionalData.fixText === ""?   requestData.instructions :  instructionTextArea.value;
        }
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
}

