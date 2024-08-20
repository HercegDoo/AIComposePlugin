export function createIconPair(labelText, inputName, inputDivId) {
    const pairDiv = document.createElement("div");
    pairDiv.className = "pairs";

    const label = document.createElement("label");
    label.setAttribute("for", inputName);
    label.textContent = labelText;

    const iconDiv = document.createElement("div");
    iconDiv.className = "icon-divs";
    iconDiv.id = inputDivId;

    const icon = document.createElement("i");
    icon.className = "fa-solid fa-circle-info";

    iconDiv.appendChild(icon);
    pairDiv.appendChild(label);
    pairDiv.appendChild(iconDiv);

    return pairDiv;
}