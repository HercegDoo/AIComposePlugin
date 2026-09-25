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

export function initComposeOptionPersistence(
  container,
  onSave,
  loadOptions,
  initialOptions
) {
  if (!container || typeof onSave !== "function") return;

  let saving = false;
  let pendingOptions = null;
  const changed = new Set();
  const observedValues = new Map();

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

  function onChange(event) {
    const select = event.target;
    if (select?.tagName !== "SELECT" || !optionIds.includes(select.id)) {
      return;
    }

    changed.add(select.id);
    if (observedValues.get(select.id) === select.value) return;
    observedValues.set(select.id, select.value);
    if (event.aicComposeHandled) return;
    event.aicComposeHandled = true;

    const options = { [select.id]: select.value };
    if (saving) {
      pendingOptions = { ...pendingOptions, ...options };
    } else {
      save(options);
    }
  }

  // Capture selections immediately, even while Roundcube is building the page.
  for (const type of ["input", "change"]) {
    container.addEventListener(type, onChange, true);
  }

  function bindRenderedOptions() {
    const wrapper = container.querySelector(".select-div");
    if (!wrapper) return;

    for (const id of optionIds) {
      for (const type of ["input", "change"]) {
        wrapper.querySelector(`#${id}`)?.addEventListener(type, onChange);
      }
    }
    for (const type of ["input", "change"]) {
      wrapper.addEventListener(type, onChange);
    }

    // A late-loaded bundle can find controls changed before its listeners ran.
    if (initialOptions && typeof initialOptions === "object") {
      for (const id of optionIds) {
        const select = wrapper.querySelector(`#${id}`);
        const field = id.replace(/^aic_/, "").replace(/_select$/, "");
        const initial = initialOptions[field];
        if (
          select?.tagName === "SELECT" &&
          typeof initial === "string" &&
          select.value.toLowerCase() !== initial.toLowerCase() &&
          !changed.has(id)
        ) {
          onChange({ target: select });
        }
      }
    }
  }

  bindRenderedOptions();
  if (container.readyState === "loading") {
    container.addEventListener("DOMContentLoaded", bindRenderedOptions, {
      once: true,
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
