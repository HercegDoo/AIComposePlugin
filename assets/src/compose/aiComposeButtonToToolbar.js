import { createComposeModal } from "./modal";
import { addDropdownToAIButton } from "./modal/addDropdownToAIButton";

export const createButtonInToolbarMenu = function () {
  const parentMenu = document.getElementById("toolbar-menu");
  const liElement1 = document.createElement("li");
  liElement1.id = "aic-button-li";
  liElement1.setAttribute("role", "menuitem");

  liElement1.innerHTML = `
<span class="dropbutton active">
<a id="aicp-prompt-open-button" class="auto-generate" role="button">
<span id="aic-button-span" class="inner">AI Compose</span>
</a>
<a href="#" id="instructionsdropdownlink" class="dropdown active" data-popup="predefinedInstructionsMenu"
                    tabIndex="0" aria-haspopup="true" aria-expanded="false" aria-owns="forward-menu"
                    data-original-title="" title="">
    <span class="inner">Forwarding options</span>
  </a>
</span>
`;
  parentMenu.append(liElement1);

  addDropdownToAIButton();

};


export const openModal = function() {
  createComposeModal();
};
