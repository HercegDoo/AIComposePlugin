
export default class ToolTipAvailability {

  static instance = null;

  constructor() {
    if (ToolTipAvailability.instance) {
      return ToolTipAvailability.instance;
    }
    this.popup = document.getElementById('fix-text-tooltip');

    this.instructionTextArea = document.getElementById("aic-instruction");
    this.textarea = document.getElementById("composebody");
    this.selectedText = "";
    this.editorHTML = null;
    this.#callCommands();

    ToolTipAvailability.instance = this;
  }

 #callCommands() {
this.#toggleGenerateButton();
this.#toggleFixTextToolTip();
  }

 #toggleGenerateButton() {
   if(this.instructionTextArea.value === ""){
     document.getElementById('aic-generate-email-button').setAttribute('disabled', 'disabled');
   }
   this.instructionTextArea.addEventListener('input', ()=>{
     if(this.instructionTextArea.value === ""){
       document.getElementById('aic-generate-email-button').setAttribute('disabled', 'disabled');
     }
     else{
       document.getElementById('aic-generate-email-button').removeAttribute('disabled');
       rcmail.enable_command('generatemail', true);
     }
   });
  }

  #toggleFixTextToolTip() {

    this.textarea.addEventListener("mouseup", (event)=>{
      const rectBasic = document.getElementById('composebody').getBoundingClientRect();
      console.log(`Koordinate obicne Textarea: top: ${rectBasic.top}, bottom: ${rectBasic.bottom}, left: ${rectBasic.left}, right: ${rectBasic.right} `);
      this.#checkSelection(event.clientX, event.clientY) });

    document.addEventListener("click", (e) => {
      if (!this.textarea.contains(e.target) && !this.popup.contains(e.target) ) {
       this.popup.style.display='none';
      }
    });

    rcmail.addEventListener("editor-load", (e) => {
      this.editorHTML = e?.ref?.editor;
      if(tinymce.activeEditor){this.#callHtmlEditorEventListeners()}
    });
  }

  #checkSelection(coordsX, coordsY) {
    let start, end;
    if (!this.isHTMLEditor()) {
      start = this.textarea.selectionStart;
      end = this.textarea.selectionEnd;
    }

    document.getElementById('fix-text-aic-instruction').value = "";
    const textareaSelectedText = this.#isTextSelected() ? this.textarea.value.substring(start, end) : "";
    this.selectedText = this.isHTMLEditor() ? (this.#isTextSelected() ? this.#setEditorHTMLSelection() : "") : textareaSelectedText;
    if (this.#isTextSelected()) {
      this.popup.style.left = `${coordsX}px`;
      this.popup.style.top = `${coordsY}px`;
      this.popup.style.display = "flex";
    } else {
      this.popup.style.display = "none";
    }
  }



  #setEditorHTMLSelection(){
    this.selectedText = this.editorHTML.selection.getContent({ format: 'text' });

  }



getSelectedText() {
    return this.selectedText;
  }

isHTMLEditor(){
    return !!(this.editorHTML && tinymce.activeEditor);
}

#isTextSelected(){
    if(!this.isHTMLEditor()){
      return this.textarea.selectionStart !== this.textarea.selectionEnd;
    }
    else {
      return this.editorHTML.selection.getContent({ format: 'text' }) !== "";
    }
    
}

  #callHtmlEditorEventListeners(){

    this.editorHTML.on('click keyup',  ()=> {

      const aboveElement = document.querySelector('#compose-content > form > div.form-group.row');
      const aboveElementClientRect = aboveElement.getBoundingClientRect();
      const editorTop = aboveElementClientRect.top + aboveElement.clientHeight + parseFloat(window.getComputedStyle(aboveElement).marginBottom);
      const editorLeft = aboveElementClientRect.left;
      this.popup.style.display = 'flex';
      const tooltipHeight = parseFloat(document.getElementById('fix-text-tooltip').clientHeight * 0.7);
      this.popup.style.display = "none";

        const selection = tinymce.activeEditor.selection;
        if (range) {
          const rect = range.getBoundingClientRect();
          this.#checkSelection(editorLeft + rect.left + window.scrollX, editorTop + rect.top + window.scrollY + tooltipHeight);
        }
      });



  }


}
