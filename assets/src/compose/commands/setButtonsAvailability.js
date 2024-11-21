
export default class ButtonsAvailability {

  static instance = null;

  constructor() {
    if (ButtonsAvailability.instance) {
      return ButtonsAvailability.instance;
    }

    this.instructionTextArea = document.getElementById("aic-instruction");
    this.fixSelectedTextButton = document.getElementById("aic-fix-text-button");
    this.textarea = document.getElementById("composebody");
    this.selectedText = "";
    this.beforeText = "";
    this.afterText = "";
    this.#callCommands();

    ButtonsAvailability.instance = this;
  }

 #callCommands() {
this.#toggleGenerateButton();
this.#toggleFixTextButton();
  }

 #toggleGenerateButton() {
   this.instructionTextArea.addEventListener('input', ()=>{
     if(this.instructionTextArea.value === ""){
       document.getElementById('aic-generate-email-button').setAttribute('disabled', 'disabled');
     }
     else{
       document.getElementById('aic-generate-email-button').removeAttribute('disabled');
     }
   });
  }

  #toggleFixTextButton() {
    document.addEventListener("selectionchange", () => {
      if (document.activeElement === this.textarea) {
        this.#checkSelection();
      }
    });

    this.textarea.addEventListener("mouseup", ()=>{this.#checkSelection()});

    document.addEventListener("click", (e) => {
      if (!this.textarea.contains(e.target) ) {

       this.fixSelectedTextButton.setAttribute('disabled', 'disabled');
      }
    });
  }

  #checkSelection() {

    const start = this.textarea.selectionStart,
      end = this.textarea.selectionEnd;

    const isTextSelected = start !== end;
    this.selectedText = isTextSelected ? this.textarea.value.substring(start, end) : "";

    this.fixSelectedTextButton.toggleAttribute("disabled", !isTextSelected);

    if(isTextSelected){
      rcmail.enable_command('openfixtextmodal', true);
        this.beforeText = this.textarea.value.substring(0, start);
         this.afterText = this.textarea.value.substring(end);

    }
  }

 getFormattedPreviousGeneratedEmail() {
    return (
      this.beforeText +
      '<strong id="focused-selected-text-test">' +
      this.selectedText +
      "</strong>" +
      this.afterText
    );
  }

getSelectedText() {
    return this.selectedText;
  }



}
