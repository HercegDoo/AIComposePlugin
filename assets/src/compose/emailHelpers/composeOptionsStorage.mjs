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
  const serverKey = `aicomposeplugin.composeOptions.server.v1.${userId}`;
  let saved = {};
  let serverSaved = false;
  let saveRevision = 0;

  try {
    serverSaved = storage?.getItem(serverKey) === "1";
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
      !serverSaved &&
      typeof value === "string" &&
      Array.from(select.options).some((option) => option.value === value)
    ) {
      select.value = value;
    }

    select.addEventListener("change", () => {
      saved[select.id] = select.value;
      try {
        storage?.setItem(key, JSON.stringify(saved));
      } catch (_) {
        // The select must remain usable when storage is unavailable.
      }

      if (typeof onSave !== "function" || selects.length !== optionIds.length)
        return;

      const options = Object.fromEntries(
        selects.map((item) => [item.id, item.value])
      );
      const revision = ++saveRevision;
      try {
        storage?.removeItem(serverKey);
      } catch (_) {
        // Server saving still works without localStorage.
      }
      try {
        Promise.resolve(onSave(options))
          .then(() => {
            if (revision !== saveRevision) return;
            try {
              storage?.setItem(serverKey, "1");
            } catch (_) {
              // Server preferences remain available when storage is blocked.
            }
          })
          .catch(() => {
            if (revision !== saveRevision) return;
            try {
              storage?.removeItem(serverKey);
            } catch (_) {
              // The local selection is still usable for this compose session.
            }
          });
      } catch (_) {
        // The local selection is still usable if a save cannot be started.
      }
    });
  }
}
