const optionIds = [
  "aic_style_select",
  "aic_length_select",
  "aic_creativity_select",
  "aic_language_select",
];

export function initComposeOptionPersistence(container, storage, userId) {
  if (!container || !storage || !userId) return;

  const selects = optionIds
    .map((id) => container.querySelector(`#${id}`))
    .filter((element) => element?.tagName === "SELECT");
  if (selects.length === 0) return;

  const key = `aicomposeplugin.composeOptions.v1.${userId}`;
  let saved = {};

  try {
    const stored = JSON.parse(storage.getItem(key) || "{}");
    if (stored && typeof stored === "object" && !Array.isArray(stored)) {
      saved = stored;
    }
  } catch (_) {
    // Storage may be disabled or contain data from an older version.
  }

  for (const select of selects) {
    const value = saved[select.id];
    if (
      typeof value === "string" &&
      Array.from(select.options).some((option) => option.value === value)
    ) {
      select.value = value;
    }

    select.addEventListener("change", () => {
      saved[select.id] = select.value;
      try {
        storage.setItem(key, JSON.stringify(saved));
      } catch (_) {
        // The select must remain usable when storage is unavailable.
      }
    });
  }
}
