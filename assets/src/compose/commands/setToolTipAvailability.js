
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
   this.instructionTextArea.addEventListener('input', ()=>{
     if(this.instructionTextArea.value === ""){
       document.getElementById('aic-generate-email-button').setAttribute('disabled', 'disabled');
     }
     else{
       document.getElementById('aic-generate-email-button').removeAttribute('disabled');
     }
   });
  }

  #toggleFixTextToolTip() {

    this.textarea.addEventListener("mouseup", ()=>{
      this.#checkSelection() });

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

  #checkSelection() {
      const start = this.textarea.selectionStart,
        end = this.textarea.selectionEnd;

      const isTextSelected = start !== end;
      this.selectedText = isTextSelected ? this.textarea.value.substring(start, end) : "";
      if(isTextSelected) {
        const { x, y } = this.#getCursorXY(this.textarea, end);
        // Funkcija koja ažurira poziciju
        const updatePosition = () => {
          const updatedValues = this.#getCursorXY(this.textarea, end);
          this.popup.style.left = `${updatedValues.x}px`;
          this.popup.style.top = `${updatedValues.y}px`;
        };

        window.addEventListener('resize', updatePosition);

        updatePosition();
        this.popup.style.top = `${y + 100}px`;

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

    // get the computed style of the input and clone it onto the dummy element
    const copyStyle = getComputedStyle(input);
    for (const prop of copyStyle) {
      div.style[prop] = copyStyle[prop];
    }

    // Replace whitespaces if it's a single-line input
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
    const inputRect = input.getBoundingClientRect();
    const negValue = inputRect.bottom - 670;
    return {
      x: spanX ,  // Calculate the x position relative to the input element
      y: spanY - inputRect.bottom + negValue  // Calculate the y position relative to the input element
    };
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
