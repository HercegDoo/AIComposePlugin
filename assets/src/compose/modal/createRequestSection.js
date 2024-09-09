import {
  createRecipientPropbox,
  createSenderPropbox,
  createStylePropbox,
  createLengthPropbox,
  createCreativityPropbox,
  createLanguagePropbox,
} from "./createPropboxes.js";
import {translation} from "../../utils";

export function createRequestSection() {
  const aicRequest = document.createElement("div");
  aicRequest.id = "aic-request";

  const properties = document.createElement("div");
  properties.className = "properties";

  properties.appendChild(createRecipientPropbox());
  properties.appendChild(createSenderPropbox());
  properties.appendChild(createStylePropbox());
  properties.appendChild(createLengthPropbox());
  properties.appendChild(createCreativityPropbox());
  properties.appendChild(createLanguagePropbox());

  aicRequest.appendChild(properties);

  const instructionsDiv = document.createElement("div");
  instructionsDiv.className = "instructions";
  instructionsDiv.innerHTML = `<div>
      <label for="aic-instructions">${translation('ai_label_instructions')}</label>
      <span class="xinfo right"><div>${translation('ai_tip_instructions')}</div></span>
  </div>
  <textarea id="aic-instructions" class="form-control"></textarea>`;

  aicRequest.appendChild(instructionsDiv);

  const generateContainer = document.createElement("div");
  generateContainer.className = "generate-container";
  generateContainer.innerHTML = `<button type="button" class="btn btn-primary"">
      <span>${translation('ai_generate_email')}</span>
      <span style="display: none;">${'ai_generate_again'}</span>
  </button>
  <button type="button" class="btn btn-default" id="instruction-example" >${translation('ai_button_show_instructions')}</button>
  <button disabled type="button" class="btn btn-default disabled" id="fixSelectedText" >${translation('ai_button_fix_selected_text')}</button>`;

  aicRequest.appendChild(generateContainer);

  return aicRequest;
}
