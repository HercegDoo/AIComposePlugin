const optionIds = [
  "aic_style_select",
  "aic_length_select",
  "aic_creativity_select",
  "aic_language_select",
];

export function initComposeOptionPersistence(container, onSave) {
  if (!container || typeof onSave !== "function") return;

  for (const id of optionIds) {
    const select = container.querySelector(`#${id}`);
    if (select?.tagName !== "SELECT") continue;

    select.addEventListener("change", () => {
      try {
        Promise.resolve(onSave({ [id]: select.value })).catch(() => {
          // The request handler reports a failed server save to the user.
        });
      } catch (_) {
        // Keep the select usable when a request cannot be started.
      }
    });
  }
}
