import "../../../compose/aic-modal-additional-style/elastic.css";
import "../../../compose/aic-modal-additional-style/larry.css";
import "../../../compose/aic-modal-additional-style/xcloud.css";
import "../../../compose/aic-modal-style/styles.css";

import { createDialogContents } from "./createDialogContents.js";

export function createComposeModal() {
    const dialogMask = document.createElement("div");
    dialogMask.id = "aic-compose-dialog";
    dialogMask.className = "xdialog-mask";
    dialogMask.style.display = "block";

    const dialogBox = document.createElement("div");
    dialogBox.className = "xdialog-box full-screen";
    dialogBox.id = "aic-compose-dialog-box";

    const dialogTitle = document.createElement("div");
    dialogTitle.className = "xdialog-title";
    dialogTitle.textContent = "Compose Message with AI";

    const closeButton = document.createElement("button");
    closeButton.className = "xdialog-close btn btn-secondary";
    closeButton.textContent = "×";

    dialogTitle.appendChild(closeButton);
    dialogBox.appendChild(dialogTitle);

    const dialogContents = createDialogContents();
    dialogBox.appendChild(dialogContents);

    dialogMask.appendChild(dialogBox);

    closeButton.addEventListener("click", function () {
        document.body.removeChild(dialogMask);
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            document.body.removeChild(dialogMask);
        }
    });

    document.body.appendChild(dialogMask);
}
