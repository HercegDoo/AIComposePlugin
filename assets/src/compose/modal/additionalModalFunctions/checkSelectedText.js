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


    textarea.addEventListener('mouseup', checkSelection);
    textarea.addEventListener('keyup', checkSelection);
    textarea.addEventListener('input', checkSelection);

}
