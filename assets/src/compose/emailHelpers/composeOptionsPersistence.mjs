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

export function initComposeOptionPersistence(container, onSave, loadOptions) {
  if (!container || typeof onSave !== "function") return;

  const selects = optionIds
    .map((id) => container.querySelector(`#${id}`))
    .filter((select) => select?.tagName === "SELECT");
  if (selects.length === 0) return;

  let saving = false;
  let pendingOptions = null;
  const changed = new Set();

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
      changed.add(select.id);
      const options = { [select.id]: select.value };
      if (saving) {
        pendingOptions = { ...pendingOptions, ...options };
      } else {
        save(options);
      }
    });
  }

  if (typeof loadOptions === "function") {
    let request;
    try {
      request = loadOptions();
    } catch (error) {
      request = Promise.reject(error);
    }

    Promise.resolve(request)
      .then((options) => {
        if (!options || typeof options !== "object") return;
        for (const select of selects) {
          if (changed.has(select.id)) continue;
          const field = select.id.replace(/^aic_/, "").replace(/_select$/, "");
          const value = options[field];
          if (typeof value !== "string") continue;
          const matchingOption = Array.from(select.options).find(
            (option) => option.value.toLowerCase() === value.toLowerCase()
          );
          if (matchingOption) select.value = matchingOption.value;
        }
      })
      .catch(() => {
        // Keep the server-rendered choices if the refresh fails.
      });
  }
}
