import { createComposeModal } from "./modal";
import { addDropdownToAIButton } from "./modal/addDropdownToAIButton";
import { translation } from "../utils";

export const createButtonInToolbarMenu = function () {
  const parentMenu = document.getElementById("toolbar-menu");
  const liElement1 = document.createElement("li");
  liElement1.id = "aic-button-li";
  liElement1.setAttribute("role", "menuitem");

  liElement1.innerHTML = `
<span class="dropbutton active">
<a id="aicp-prompt-open-button" class="auto-generate" role="button"   title="${translation('ai_dialog_title')}">
<span id="aic-button-span" class="inner">AI</span>
</a>
<a href="#" id="instructionsdropdownlink" class="dropdown active" data-popup="predefinedInstructionsMenu"
                    tabIndex="0" aria-haspopup="true" aria-expanded="false" 
                    data-original-title="" title="${translation('ai_predefined_use_predefined_instructions')}">
    <span class="inner">${translation('ai_predefined_section_title')}</span>
  </a>
</span>
`;
  parentMenu.append(liElement1);

  addDropdownToAIButton();

};


export const openModal = function() {
  createComposeModal();
};
