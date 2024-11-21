
export default class ButtonsAvailability {

  constructor() {
   this.instructionTextArea =  document.getElementById('aic-instruction');
    this.fixSelectedTextButton = document.getElementById("aic-fix-text-button");
    this.textarea =  document.getElementById('composebody');
    this.selectedText = "";
    this.#callCommands();
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

    const isTextSelected = start !== end;  // Proveravamo da li je tekst selektovan
    this.selectedText = isTextSelected ? this.textarea.value.substring(start, end) : "";

    this.fixSelectedTextButton.toggleAttribute("disabled", !isTextSelected);
  }


}
