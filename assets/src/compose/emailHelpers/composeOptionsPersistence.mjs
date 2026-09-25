const optionIds = [
  "aic_style_select",
  "aic_length_select",
  "aic_creativity_select",
  "aic_language_select",
];

export function composeOptionsPostData(options) {
  const aic = {};
  for (const id of optionIds) {
    if (typeof options[id] === "string") {
      aic[id.replace(/^aic_/, "").replace(/_select$/, "")] = options[id];
    }
  }

  return { data: { aic } };
}

export function collectComposeOptions(container) {
  const options = {};
  for (const id of optionIds) {
    const select = container.querySelector(`#${id}`);
    if (select?.tagName !== "SELECT" || !select.value) return null;
    options[id] = select.value;
  }

  return options;
}

export function initComposeOptionsSaveButton(
  container,
  onSave,
  onSuccess,
  onError
) {
  if (!container || typeof onSave !== "function") return;

  let saving = false;
  // Delegation works even though the script loads before Roundcube renders the sidebar.
  container.addEventListener(
    "click",
    async (event) => {
      const button = event.target?.closest?.("#aic-save-compose-options");
      if (!button) return;

      event.preventDefault();
      if (saving) return;

      const options = collectComposeOptions(container);
      if (!options) {
        onError?.();
        return;
      }

      saving = true;
      button.disabled = true;
      try {
        await onSave(options);
        onSuccess?.();
      } catch (error) {
        onError?.();
      } finally {
        saving = false;
        button.disabled = false;
      }
    },
    true
  );
}
