export function createHelpSection() {
    const helpDiv = document.createElement("div");
    helpDiv.id = "aic-compose-help";
    helpDiv.style.display = "none"; // Sakriva cijeli dio Help & Examples

    helpDiv.innerHTML = `<div class="help-header">
      <button type="button" class="btn btn-primary"  style="display:none;">&lt; Back</button>
      <h3>Help & Examples</h3>
  </div>
  <div class="help-content">
      <p>Tell the AI about the email you'd like to write...</p>
      <!-- Help content and examples go here -->
  </div>`;

    return helpDiv;
}
