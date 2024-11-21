
export default class ButtonsAvailability {

  static instance = null;

  constructor() {
    if (ButtonsAvailability.instance) {
      return ButtonsAvailability.instance;
    }

    this.instructionTextArea = document.getElementById("aic-instruction");
    this.textarea = document.getElementById("composebody");
    this.selectedText = "";
    this.editorHTML = null;
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

    this.textarea.addEventListener("mouseup", ()=>{console.log(3); });

    document.addEventListener("click", (e) => {
      if (!this.textarea.contains(e.target) ) {
       this.fixSelectedTextButton.setAttribute('disabled', 'disabled');
      }
    });

    rcmail.addEventListener("editor-load", (e) => {
      this.editorHTML = e?.ref?.editor;
      if(tinymce.activeEditor){this.#callHtmlEditorEventListeners()}
    });
  }

  #checkSelection() {
      const start = this.textarea.selectionStart,
        end = this.textarea.selectionEnd;

      const isTextSelected = start !== end;
      this.selectedText = isTextSelected ? this.textarea.value.substring(start, end) : "";

  }

  #checkEditorHTMLSelection(){
    this.selectedText = this.editorHTML.selection.getContent({ format: 'text' });

  }


getSelectedText() {
    return this.selectedText;
  }

getActiveEditor(){
    return !!(this.editorHTML && tinymce.activeEditor);
}

  #callHtmlEditorEventListeners(){
    this.editorHTML.on('selectionchange', () => {
     this.#checkEditorHTMLSelection();
    });

    this.editorHTML.on('mouseup', () => {
      this.#checkEditorHTMLSelection();
    });

  }


}
