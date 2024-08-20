export function createInstructions(modal) {
    const aicInstructions = document.createElement("div");
    aicInstructions.id = "aic-instructions";

    const instructionPairElements = document.createElement("div");
    instructionPairElements.id = "aic-instructions-pair-elements";

    const basicDiv = document.createElement("div");

    const instructionLabel = document.createElement("label");
    instructionLabel.setAttribute("for", "aic-instruction-textarea");
    instructionLabel.textContent = "Instructions";

    const instructionIconDiv = document.createElement("div");
    instructionIconDiv.className = "icon-divs";
    instructionIconDiv.id = "instruction-icon";

    const instructionIcon = document.createElement("i");
    instructionIcon.className = "fa-solid fa-circle-info";

    instructionIconDiv.appendChild(instructionIcon);
    basicDiv.appendChild(instructionLabel);
    basicDiv.appendChild(instructionIconDiv);
    instructionPairElements.appendChild(basicDiv);

    const instructionsTextarea = document.createElement("textarea");
    instructionsTextarea.id = "aic-instruction-textarea";
    instructionsTextarea.name = "aic-instruction-textarea";

    instructionPairElements.appendChild(instructionsTextarea);
    aicInstructions.appendChild(instructionPairElements);

    modal.appendChild(aicInstructions);
}