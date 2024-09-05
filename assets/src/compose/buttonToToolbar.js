import { createComposeModal } from "./modal";

export const createButtonInToolbarMenu = function () {
  const parentMenu = document.getElementById("toolbar-menu");
  const liElement1 = document.createElement("li");
  liElement1.id = "aic-button-li";
  liElement1.setAttribute("role", "menuitem");

  liElement1.innerHTML = `
<a id="aicp-prompt-open-button" class="auto-generate responses active" role="button">
<span id="aic-button-span" class="inner">AI Compose</span>
</a>`;

  parentMenu.append(liElement1);
};

export const openModal = function () {
  createComposeModal();
};
