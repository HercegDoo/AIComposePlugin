export function checkSelectedText(){
    const textarea = document.querySelector("#aic-email")
    const fixSelectedTextButton = document.getElementById("fixSelectedText");

    function checkSelection() {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;

        if (start !== end) {
            const selectedText = textarea.value.substring(start,end);
            fixSelectedTextButton.classList.remove("disabled-button");
            fixSelectedTextButton.classList.add("enabled-button");
        } else {
            if(fixSelectedTextButton.classList.contains("enabled-button")
            ){
                fixSelectedTextButton.classList.remove("enabled-button");
                fixSelectedTextButton.classList.add("disabled-button");}
        }
    }

    textarea.addEventListener('mouseup', checkSelection);
}
