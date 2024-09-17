let savedSelectedText = "";

export function checkSelectedText() {
  const textarea = document.querySelector("#aic-email");
  const fixSelectedTextButton = document.getElementById("fixSelectedText");
  const instructionsTextArea = document.getElementById('aic-instructions');

  function checkSelection() {
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;

    const isTextSelected = start !== end && instructionsTextArea.value !== '';
    const selectedText = isTextSelected
      ? textarea.value.substring(start, end)
      : "";

    if (isTextSelected) {
      savedSelectedText = selectedText;
    }

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

  document.addEventListener("selectionchange", () => {
    if (document.activeElement === textarea) {
      checkSelection();
    }
  });

  //Klik van textarea
  document.addEventListener("click", (e) => {
    e.stopPropagation();
    if (!textarea.contains(e.target)) {
      disableButton();
    }
  });
}

export function getSelectedText() {
  return savedSelectedText;
}
