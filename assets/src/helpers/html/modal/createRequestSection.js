import {
    createRecipientPropbox,
    createSenderPropbox,
    createStylePropbox,
    createLengthPropbox,
    createCreativityPropbox,
    createLanguagePropbox,
} from "./createPropboxes.js";

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
      <label for="aic-instructions">Instructions</label>
      <span class="fa-solid fa-circle-info xinfo right"><div>Instructions on what to include in the new email. Click the Instruction Examples button to see some examples.</div></span>
  </div>
  <textarea id="aic-instructions" class="form-control" placeholder="Example: ask to change the appointment for next week"></textarea>`;

    aicRequest.appendChild(instructionsDiv);

    const generateContainer = document.createElement("div");
    generateContainer.className = "generate-container";
    generateContainer.innerHTML = `<button type="button" class="btn btn-primary"">
      <span>Generate Email</span>
      <span style="display: none;">Generate Again</span>
  </button>
  <button type="button" class="btn btn-default" >Instruction Examples</button>`;

    aicRequest.appendChild(generateContainer);

    return aicRequest;
}
