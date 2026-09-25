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

  let saving = false;
  let pendingOptions = null;
  const changed = new Set();

  function applySavedOptions(options) {
    for (const id of optionIds) {
      const select = container.querySelector(`#${id}`);
      if (select?.tagName !== "SELECT" || changed.has(id)) continue;

      const field = id.replace(/^aic_/, "").replace(/_select$/, "");
      const value = options[field];
      if (typeof value !== "string") continue;

      const matchingOption = Array.from(select.options).find(
        (option) => option.value.toLowerCase() === value.toLowerCase()
      );
      if (matchingOption) select.value = matchingOption.value;
    }
  }

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

  container.addEventListener(
    "change",
    (event) => {
      const select = event.target;
      if (select?.tagName !== "SELECT" || !optionIds.includes(select.id)) {
        return;
      }

      changed.add(select.id);
      const options = { [select.id]: select.value };
      if (saving) {
        pendingOptions = { ...pendingOptions, ...options };
      } else {
        save(options);
      }
    },
    true
  );

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
        applySavedOptions(options);

        const Observer = container.defaultView?.MutationObserver;
        const savedIds = optionIds.filter((id) => {
          const field = id.replace(/^aic_/, "").replace(/_select$/, "");
          return typeof options[field] === "string";
        });
        if (
          Observer &&
          savedIds.some((id) => !container.querySelector(`#${id}`))
        ) {
          const observer = new Observer(() => {
            applySavedOptions(options);
            if (savedIds.every((id) => container.querySelector(`#${id}`))) {
              observer.disconnect();
            }
          });
          observer.observe(container.documentElement || container, {
            childList: true,
            subtree: true,
          });
        }
      })
      .catch(() => {
        // Keep the server-rendered choices if the refresh fails.
      });
  }
}
