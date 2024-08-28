export function checkSelectedText() {
    const textarea = document.querySelector("#aic-email");
    const fixSelectedTextButton = document.getElementById("fixSelectedText");

    function checkSelection() {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;

        if (start !== end) {
            const selectedText = textarea.value.substring(start, end);
            fixSelectedTextButton.classList.remove("disabled");
            fixSelectedTextButton.removeAttribute("disabled");
        } else {
            fixSelectedTextButton.setAttribute("disabled","disabled");
            fixSelectedTextButton.classList.add("disabled");
        }
    }

    textarea.addEventListener('mouseup', checkSelection);
    textarea.addEventListener('keyup', checkSelection);
    textarea.addEventListener('input', checkSelection);

}
