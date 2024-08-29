import "./styles.css";
import { createDialogContents } from "./createDialogContents.js";
import {checkSelectedText} from "./additionalModalFunctions/checkSelectedText";
import {regulateHelpModal} from "./additionalModalFunctions/showHelpModal";

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

    checkSelectedText();
    regulateHelpModal();

    $("select:not([multiple])", dialogMask).each(function () {
        UI.pretty_select(this);
    });
}
