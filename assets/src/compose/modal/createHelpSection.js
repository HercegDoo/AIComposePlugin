export function createHelpSection() {
    const helpDiv = document.createElement("div");
    helpDiv.id = "aic-compose-help";
    helpDiv.setAttribute("hidden", "true");

    helpDiv.innerHTML = `<div class="help-header">
      <button type="button" class="btn btn-primary">&lt; Back</button>
      <h3>Help & Examples</h3>
  </div>
  <div class="help-content">
      <p>Tell the AI about the email you'd like to write...</p>
      <!-- Help content and examples go here -->
  </div>`;

    return helpDiv;
}
