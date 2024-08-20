import { createIconPair } from "./createIconPair";

export function createInputDiv(
    labelText,
    inputId,
    inputName,
    inputDivId,
    inputType = "text"
) {
    const inputDiv = document.createElement("div");
    inputDiv.className = "aic-info-input";

    const pairDiv = createIconPair(labelText, inputName, inputDivId);

    let input;
    if (inputType === "select") {
        input = document.createElement("select");
        input.appendChild(new Option("Opcija 1", "opcija1"));
        input.appendChild(new Option("Opcija 2", "opcija2"));
        input.appendChild(new Option("Opcija 3", "opcija3"));
    } else {
        input = document.createElement("input");
        input.type = inputType;
    }
    input.name = inputName;
    input.id = inputId;

    inputDiv.appendChild(pairDiv);
    inputDiv.appendChild(input);

    return inputDiv;
}