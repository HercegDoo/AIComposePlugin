
import { getRequestDataFields } from "../emailHelpers/requestDataHandler";
import { getPreviousGeneratedInsertedEmail, insertEmail } from "../emailHelpers/insertEmailHandler";
import {
  signatureCheckedPreviousConversation,
  stripGeneratedClosing,
} from "../emailHelpers/signaturesHandler";
import { translation } from "../../utils";
import { stripGeneratedHtmlClosing } from "../emailHelpers/htmlEmail";
import { display_messages, errorPresent, validateFields } from "../emailHelpers/validateFields";
import { getSubject, setSubject } from "../emailHelpers/subjectHandler";

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
    const previousGeneratedText = getPreviousGeneratedInsertedEmail();
    requestData.previousGeneratedEmail = requestData.htmlMode === "1"
      ? getPreviousGeneratedInsertedEmail("html") || previousGeneratedText
      : previousGeneratedText;
    const previousConversationObject = signatureCheckedPreviousConversation(previousGeneratedText);
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
        "plugin.aicomposeplugin_GenereteEmailAction",
        {
          senderName: `${requestData.senderName}`,
          recipientName: `${requestData.recipientName}`,
          instructions: `${requestData.instructions}`,
          style: `${requestData.style}`,
          length: `${requestData.length}`,
          htmlMode: requestData.htmlMode,
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
        if (!data || data.status !== "success") {
          return;
        }
        const response = data && data["respond"] !== undefined ? data["respond"] : "";
        insertEmail(
          requestData.signaturePresent
            ? requestData.htmlMode === "1"
              ? stripGeneratedHtmlClosing(response, requestData.senderName, previousConversationObject.signatureText)
              : stripGeneratedClosing(response, requestData.senderName, previousConversationObject.signatureText)
            : response,
          requestData.htmlMode === "1"
        );
        if (!requestData.subject.trim() && !getSubject().trim() && data.subject) {
          setSubject(data.subject);
        }
        if (data.subjectError) {
          rcmail.display_message(translation("ai_request_error"), "warning");
        }
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
