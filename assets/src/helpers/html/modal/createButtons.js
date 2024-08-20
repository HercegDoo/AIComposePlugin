export function createButtons(modal) {
    const aicButtons = document.createElement("div");
    aicButtons.id = "aic-buttons";

    const generateEmailButton = document.createElement("button");
    generateEmailButton.id = "aic-generate-email";
    generateEmailButton.textContent = "Generate Email";

    const instructionExamplesButton = document.createElement("button");
    instructionExamplesButton.id = "aic-instruction-examples";
    instructionExamplesButton.textContent = "Instruction examples";

    aicButtons.appendChild(generateEmailButton);
    aicButtons.appendChild(instructionExamplesButton);

    modal.appendChild(aicButtons);
}