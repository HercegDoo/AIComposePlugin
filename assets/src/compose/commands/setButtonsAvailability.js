
export default class ButtonsAvailability {

  static instance = null;

  constructor() {
    if (ButtonsAvailability.instance) {
      return ButtonsAvailability.instance;
    }
    this.popup = document.createElement('div');
    this.popup.id = 'popup649';
    this.popup.style.position = 'absolute';
    this.popup.style.backgroundColor = 'red';
    this.popup.style.border = '1px solid #ccc';
    this.popup.style.padding = '8px';
    this.popup.style.borderRadius = '4px';
    this.popup.style.boxShadow = '0px 2px 5px rgba(0,0,0,0.2)';
   this.popup.innerText = 'Ovo je popup ispod kursora!';
    document.body.appendChild(this.popup);
    this.tooltip = document.getElementById('Kanta');
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
        console.log("pozvanb selectionchange");
        // this.#checkSelection()
      }
    });

    this.textarea.addEventListener("mouseup", ()=>{
console.log("Pozvan mouseup")
      this.#checkSelection() });

    document.addEventListener("click", (e) => {
      if (!this.textarea.contains(e.target) ) {
       this.fixSelectedTextButton.setAttribute('disabled', 'disabled');
        document.getElementById('popup649').style.display = 'none';
      }
    });

    this.textarea.addEventListener('click', ()=>{

    })



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
          console.log(updatedValues);
          this.popup.style.left = `${updatedValues.x}px`;
          this.popup.style.top = `${updatedValues.y}px`;
        };

        // Dodajemo event listener za resize da ažuriramo poziciju popup-a
        window.addEventListener('resize', updatePosition);

        // Prva pozicija kada je tekst selektovan
        updatePosition();
        this.popup.style.top = `${y + 100}px`;

       this.popup.style.display = "block";
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
    console.log(`inputRectBottom: ${inputRect.bottom}`);
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
