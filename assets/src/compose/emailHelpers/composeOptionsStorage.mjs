const optionIds = [
  "aic_style_select",
  "aic_length_select",
  "aic_creativity_select",
  "aic_language_select",
];

export function initComposeOptionPersistence(
  container,
  storage,
  userId,
  onSave
) {
  if (!container || !userId) return;

  const selects = optionIds
    .map((id) => container.querySelector(`#${id}`))
    .filter((element) => element?.tagName === "SELECT");
  if (selects.length === 0) return;

  const key = `aicomposeplugin.composeOptions.v1.${userId}`;
  let saved = {};

  try {
    const stored = JSON.parse(storage?.getItem(key) || "{}");
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
      const options = Object.fromEntries(
        selects.map((item) => [item.id, item.value])
      );
      saved = { ...saved, ...options };
      try {
        storage?.setItem(key, JSON.stringify(saved));
      } catch (_) {
        // The select must remain usable when storage is unavailable.
      }

      if (typeof onSave !== "function" || selects.length !== optionIds.length)
        return;

      try {
        Promise.resolve(onSave(options)).catch(() => {
          // The latest local selection remains available if server saving fails.
        });
      } catch (_) {
        // The local selection is still usable if a save cannot be started.
      }
    });
  }
}
