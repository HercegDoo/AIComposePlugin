import ToolTipAvailability from "./setToolTipAvailability";


export default class FixTextCommands {

  constructor() {
    this.fixTextTextarea =  document.getElementById('fix-text-aic-instruction');
    this.sendButton = document.getElementById('fix-text-send')
    this.#registerCommands();
  }

  #registerCommands() {
    rcube_webmail.prototype.prepareFixTextRequest = this.#prepareFixTextRequest.bind(this);
    rcmail.register_command('prepareFixTextRequest');
    this.#setEventListeners();
  }

  #toggleSendButtonAvailability() {
   if(this.fixTextTextarea.value !== ""){
     rcmail.enable_command('prepareFixTextRequest', true);
     this.sendButton.removeAttribute('disabled');
   }
   else{
     this.sendButton.setAttribute('disabled', 'disabled');
   }
  }

  #prepareFixTextRequest() {

    if(this.fixTextTextarea.value !== ""){
      const fixText = new ToolTipAvailability().getSelectedText();
      const additionalData = {
        passedInstruction : this.fixTextTextarea.value,
        fixText: fixText
      }
      document.getElementById('fix-text-tooltip').style.display="none";
      return rcmail.command('generatemail', additionalData);
    }
  }

  #setEventListeners(){
    this.fixTextTextarea.addEventListener('input', ()=>{
      this.#toggleSendButtonAvailability();
    })
  }
}
