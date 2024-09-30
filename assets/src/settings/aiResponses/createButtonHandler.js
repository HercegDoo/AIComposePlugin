import {
  hideDeleteButton,
  removeFocus,
  showInputFields,
} from "./displayHandler";
import { previousSelectedRow } from "./messageClickHandler";

export function regulateCreateButton() {
  const createButton = document.querySelector('li[role="menuitem"] a.create');

  createButton.addEventListener("click", () => {
    showInputFields();
    hideDeleteButton();

    if (previousSelectedRow) {
      removeFocus(previousSelectedRow);
    }
    const hiddenInput = document.getElementById("hidden-input");
    hiddenInput.setAttribute("value", `${rcmail.env.request_token}`);
    const submit = document.getElementById("responses-submit");
    document.getElementById("edit-id").value = "";
    submit.addEventListener("click", (e) => {
      e.stopPropagation();
    });
  });
}
