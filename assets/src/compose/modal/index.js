import "./styles.css";
import { createDialogContents } from "./createDialogContents.js";
import { checkSelectedText } from "./additionalModalFunctions/selectedTextHandler";
import { regulateHelpModal } from "./additionalModalFunctions/regulateHelpModal";
import { createElementWithClassName, translation as t } from "../../utils";
import { sendDefaultPostRequest } from "./additionalModalFunctions/requests/defaultPostRequest";
import { validateFields } from "./additionalModalFunctions/fieldsValidation";
import { regulateFixTextModal } from "./additionalModalFunctions/regulateFixTextModal";
import { insertEmail } from "./additionalModalFunctions/insertEmailHandler";
import { regulatePredefinedInstructionsModal } from "./additionalModalFunctions/predefinedInstructions/regulatePredefinedInstructionsModal";
import {  setFilled } from "./additionalModalFunctions/predefinedInstructions/regulatePredefinedInstructionsModal";

export function createComposeModal() {
  const dialogMask = createElementWithClassName('div', "xdialog-mask" );
  dialogMask.id = "aic-compose-dialog";
  dialogMask.style.display = "block";

  const dialogBox = createElementWithClassName('div', "xdialog-box full-screen" );
  dialogBox.id = "aic-compose-dialog-box";

  const dialogTitle = createElementWithClassName('div', "xdialog-title" );
  dialogTitle.textContent = t("ai_dialog_title");

  const closeButton = createElementWithClassName('button', "xdialog-close btn btn-secondary" );
  closeButton.textContent = "×";

  dialogTitle.appendChild(closeButton);
  dialogBox.appendChild(dialogTitle);

  const dialogContents = createDialogContents();
  dialogBox.appendChild(dialogContents);

  dialogMask.appendChild(dialogBox);

  closeButton.addEventListener("click", function () {
    setFilled(false);
    document.body.removeChild(dialogMask);
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      setFilled(false);
      document.body.removeChild(dialogMask);
    }
  });

  document.body.appendChild(dialogMask);
  validateFields();
  sendDefaultPostRequest();
  insertEmail();
  checkSelectedText();
  regulateHelpModal();
  regulatePredefinedInstructionsModal();
  regulateFixTextModal();

  $("select:not([multiple])", dialogMask).each(function () {
    UI.pretty_select(this);
  });
}

