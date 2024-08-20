import { createInputDiv } from "./createInputDiv";

export function createEnterInfo(modal) {
    const aicEnterInfo = document.createElement("div");
    aicEnterInfo.id = "aic-enter-info";

    const recipientNameDiv = createInputDiv(
        "Recipient's name",
        "aic-recipient-name-input",
        "aic-recipient-name-input",
        "recipient-icon"
    );
    const senderNameDiv = createInputDiv(
        "Sender's name",
        "aic-sender-name-input",
        "sender-name",
        "sender-icon"
    );
    const styleDiv = createInputDiv(
        "Style",
        "aic-style-input",
        "style",
        "style-icon",
        "select"
    );
    const lengthDiv = createInputDiv(
        "Length",
        "aic-length-input",
        "length",
        "length-icon",
        "select"
    );
    const creativityDiv = createInputDiv(
        "Creativity",
        "aic-creativity-input",
        "creativity",
        "creativity-icon",
        "select"
    );
    const languageDiv = createInputDiv(
        "Language",
        "aic-language-input",
        "language",
        "language-icon",
        "select"
    );

    aicEnterInfo.appendChild(recipientNameDiv);
    aicEnterInfo.appendChild(senderNameDiv);
    aicEnterInfo.appendChild(styleDiv);
    aicEnterInfo.appendChild(lengthDiv);
    aicEnterInfo.appendChild(creativityDiv);
    aicEnterInfo.appendChild(languageDiv);

    modal.appendChild(aicEnterInfo);
}