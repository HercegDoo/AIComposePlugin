export function createGeneratedText(modal) {
    const aicGeneratedText = document.createElement("div");
    aicGeneratedText.id = "aic-generated-text";

    const penIcon = document.createElement("i");
    penIcon.className = "fa-solid fa-pen-to-square fa-xl";
    penIcon.style.color = "#e00606";

    const generatedTextareaDiv = document.createElement("div");
    generatedTextareaDiv.id = "aic-generated-mail-textarea-div";

    const generatedTextarea = document.createElement("textarea");
    generatedTextarea.id = "aic-generated-text-textarea";

    generatedTextareaDiv.appendChild(generatedTextarea);
    aicGeneratedText.appendChild(penIcon);
    aicGeneratedText.appendChild(generatedTextareaDiv);

    modal.appendChild(aicGeneratedText);
}