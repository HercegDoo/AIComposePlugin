export function checkSelectedText() {
  const textarea = document.querySelector("#aic-email");
  const fixSelectedTextButton = document.getElementById("fixSelectedText");

  function checkSelection() {
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;

    const isTextSelected = start !== end;
    const selectedText = textarea.value.substring(start, end);


    fixSelectedTextButton.classList.toggle("disabled", !isTextSelected);
    fixSelectedTextButton.toggleAttribute("disabled", !isTextSelected);
  }

  function disableButton() {
    fixSelectedTextButton.classList.add("disabled");
    fixSelectedTextButton.setAttribute("disabled", true);
  }

  textarea.addEventListener("mouseup", checkSelection);
  textarea.addEventListener("keyup", checkSelection);
  textarea.addEventListener("input", checkSelection);

  document.addEventListener('selectionchange', () => {
    if(document.activeElement === textarea){
      checkSelection();
    }
  })
  document.addEventListener('click', (e)=>{
    e.stopPropagation();
    if(!textarea.contains(e.target)){
      disableButton();
    }
  })
}
