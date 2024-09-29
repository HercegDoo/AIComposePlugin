import { translation } from "../../utils";

export function createPredefinedInstructionsSection() {
  const predefinedInstructionsDiv = document.createElement("div");
  predefinedInstructionsDiv.id = "aic-compose-predefined";
  predefinedInstructionsDiv.setAttribute("hidden", "true");

  predefinedInstructionsDiv.innerHTML = `<div class="predefined-instructions-header">
      <button type="button" class="btn btn-primary" id="predefined-back-btn">${translation("ai_help_button_back")}</button>
      <h3>${translation("ai_help_title")}</h3>
  </div>
<div class="predefined-instructions-content">
  <p>${translation("ai_help_info")}</p>
  
 </div>`;




  return predefinedInstructionsDiv;
}
