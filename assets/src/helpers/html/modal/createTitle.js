export function createTitle(modal) {
    const aicTitle = document.createElement("div");
    aicTitle.id = "aic-title";
    aicTitle.textContent = "Compose Message with AI";

    const closeButton = document.createElement("button");
    closeButton.id = "aic-close-button";
    closeButton.textContent = "X";
    aicTitle.appendChild(closeButton);

    modal.appendChild(aicTitle);
}