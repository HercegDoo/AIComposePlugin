

export default class FixTextCommands {

  constructor() {
    this.fixTextTextarea =  document.getElementById('fix-text-aic-instruction');
    this.sendButton = document.getElementById('fix-text-send')
    this.#registerCommands();
  }

  #registerCommands() {
    rcube_webmail.prototype.prepareFixTextRequest = this.#prepareFixTextRequest;
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
   console.log("Fix text komanda");
  }

  #setEventListeners(){
    this.fixTextTextarea.addEventListener('input', ()=>{
      console.log('Input okinut');
      this.#toggleSendButtonAvailability();
    })
  }
}
