import ButtonsAvailability from "./setToolTipAvailability";

export default class FixTextModalButtonCommands {


  constructor() {
    this.selectedTextTextarea = null;
    this.fixInstructionsTextArea = null;
    this.#registerCommands();
  }

  #registerCommands() {
    rcube_webmail.prototype.openfixtextmodal = this.#openfixtextmodal;
    rcube_webmail.prototype.closefixtextmodal= this.#closefixtextmodal;


    rcmail.register_command('openfixtextmodal');

    rcmail.enable_command('closefixtextmodal', true);
    rcmail.register_command('closefixtextmodal');
  }

  #openfixtextmodal() {
    const buttonsAvailability  = new ButtonsAvailability();
    document.getElementById('aic-fix-text-modal-mask').removeAttribute( 'hidden');
    this.selectedTextTextarea = document.getElementById("selected-text-test");
    this.fixInstructionsTextArea = document.getElementById("fix-instructions-test");
    this.selectedTextTextarea.innerHTML =  buttonsAvailability.getFormattedPreviousGeneratedEmail();
    this.fixInstructionsTextArea.value = "";
  }

  #closefixtextmodal() {
    document.getElementById('aic-fix-text-modal-mask').setAttribute('hidden', 'hidden');
  }
}
