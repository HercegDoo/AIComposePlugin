const optionIds = [
  "aic_style_select",
  "aic_length_select",
  "aic_creativity_select",
  "aic_language_select",
];

export function initComposeOptionPersistence(container, onSave) {
  if (!container || typeof onSave !== "function") return;

  const selects = optionIds
    .map((id) => container.querySelector(`#${id}`))
    .filter((select) => select?.tagName === "SELECT");
  let saving = false;
  let pendingOptions = null;

  function save(options) {
    saving = true;
    let request;
    try {
      request = onSave(options);
    } catch (error) {
      request = Promise.reject(error);
    }

    Promise.resolve(request)
      .catch(() => {
        // The request handler reports a failed server save to the user.
      })
      .then(() => {
        saving = false;
        if (pendingOptions) {
          const nextOptions = pendingOptions;
          pendingOptions = null;
          save(nextOptions);
        }
      });
  }

  for (const select of selects) {
    select.addEventListener("change", () => {
      const options = Object.fromEntries(
        selects.map((item) => [item.id, item.value])
      );
      if (saving) {
        pendingOptions = options;
      } else {
        save(options);
      }
    });
  }
}
