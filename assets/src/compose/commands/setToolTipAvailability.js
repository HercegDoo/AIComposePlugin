
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

  #checkSelection(clientX, clientY) {
    let start, end;
    if (!this.isHTMLEditor()) {
      start = this.textarea.selectionStart;
      end = this.textarea.selectionEnd;
    }

    document.getElementById('fix-text-aic-instruction').value = "";
    const textareaSelectedText = this.#isTextSelected() ? this.textarea.value.substring(start, end) : "";
    this.selectedText = this.isHTMLEditor() ? (this.#isTextSelected() ? this.#setEditorHTMLSelection() : "") : textareaSelectedText;
    if (this.#isTextSelected()) {
      const { x, y } = this.#getCursorXY(this.textarea, end);
      // Funkcija koja ažurira poziciju


      this.popup.style.left = `${clientX}px`;
      this.popup.style.top = `${clientY}px`;

      this.popup.style.display = "flex";
    } else {
      this.popup.style.display = "none";
    }
  }

  /**
   * returns x, y coordinates for absolute positioning of a span within a given text input
   * at a given selection point
   * @param {object} input - the input element to obtain coordinates for
   * @param {number} selectionPoint - the selection point for the input
   */
  #getCursorXY(input, selectionPoint) {
    // create a dummy element that will be a clone of our input
    const div = document.createElement('div');
    let copyStyle;
    if(!this.isHTMLEditor()){
    copyStyle = getComputedStyle(input);
    }
    else{
      const element = this.editorHTML.selection.getNode();
        copyStyle = window.getComputedStyle(element);
    }
    for (const prop of copyStyle) {
      div.style[prop] = copyStyle[prop];
    }

    // Replace whitespaces if it's a single-line input
    if(!this.isHTMLEditor()){

    }
    const swap = '.';
    const inputValue = input.tagName === 'INPUT' ? input.value.replace(/ /g, swap) : input.value;

    // Set the div content to that of the input or textarea up until selection
    const textContent = inputValue.substr(0, selectionPoint);
    div.textContent = textContent;

    // Handle styles based on input type
    if (input.tagName === 'TEXTAREA') div.style.height = 'auto';
    if (input.tagName === 'INPUT') div.style.width = 'auto';

    // Create a marker element to obtain the caret position
    const span = document.createElement('span');
    span.textContent = inputValue.substr(selectionPoint) || '.';

    // Append the span marker to the div
    div.appendChild(span);

    // Append the dummy element to the body
    document.body.appendChild(div);

    // Get the position of the span element
    const { offsetLeft: spanX, offsetTop: spanY } = span;

    // Remove the dummy element from the document
    document.body.removeChild(div);

    // Return the coordinates relative to the input element
    const inputRect =input.getBoundingClientRect();
    const negValue = inputRect.bottom - 670;
    return {
      x: spanX ,  // Calculate the x position relative to the input element
      y: spanY - inputRect.bottom + negValue  // Calculate the y position relative to the input element
    };
  }

  #getEditorHTMLCursorXY(input, selectionPoint) {

    // create a dummy element that will be a clone of our input
    const div = document.createElement('div');
    let copyStyle;

      const element = this.editorHTML.selection.getNode();
      copyStyle = window.getComputedStyle(element);

    for (const prop of copyStyle) {
      div.style[prop] = copyStyle[prop];
    }


    const editor = tinymce.activeEditor;
    const swap = '.';

// Dobijanje teksta iz TinyMCE editor-a (obični tekst bez HTML-a)
    let editorContent = editor.getBody().innerText;

// Zamenite sve razmake sa tačkom
    editorContent = editorContent.replace(/ /g, swap);

// Sada možete vratiti izmenjeni tekst u editor
 div.textContent = editorContent;


    // Handle styles based on input type
    if (input.tagName === 'TEXTAREA') div.style.height = 'auto';
    if (input.tagName === 'INPUT') div.style.width = 'auto';

    // Create a marker element to obtain the caret position
    const span = document.createElement('span');
    span.textContent =  editorContent.substr(selectionPoint) || '.';

    // Append the span marker to the div
    div.appendChild(span);

    // Append the dummy element to the body
    document.body.appendChild(div);

    // Get the position of the span element
    const { offsetLeft: spanX, offsetTop: spanY } = span;

    // Remove the dummy element from the document
    document.body.removeChild(div);

    // Return the coordinates relative to the input element
    const inputRect = document.querySelector('#composebody_ifr').getBoundingClientRect();
    const negValue = inputRect.bottom - 670;
    return {
      x: spanX ,  // Calculate the x position relative to the input element
      y: spanY - inputRect.bottom + negValue  // Calculate the y position relative to the input element
    };

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


    this.editorHTML.on('mouseup', (event) => {
      this.#checkSelection(event.clientX, event.clientY);
    });

  }


}
