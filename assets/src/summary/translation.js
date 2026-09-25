const action = "plugin.aicomposeplugin_TranslateMessageAction";

function label(key, fallback) {
  const text = rcmail.get_label(`aicomposeplugin.${key}`);
  return text === `aicomposeplugin.${key}` ? fallback : text;
}

function translatePart(uid, mailbox, locale, index) {
  return new Promise((resolve, reject) => {
    rcmail
      .http_post(action, { uid, mailbox, locale, index: String(index) })
      .done((data) => {
        if (
          data?.status === "success" &&
          data.index === index &&
          Number.isInteger(data.totalChunks) &&
          data.totalChunks > index &&
          data.totalChunks <= 100 &&
          /^[a-f0-9]{64}$/.test(data.sourceHash) &&
          typeof data.translation === "string" &&
          data.translation.trim()
        ) {
          resolve(data);
        } else {
          const error = new Error("Translation unavailable");
          error.code = data?.code;
          reject(error);
        }
      })
      .fail(reject);
  });
}

export async function translateAllParts(
  uid,
  mailbox,
  locale,
  onProgress = () => {}
) {
  const translated = [];
  let total = 1;
  let sourceHash = null;
  for (let index = 0; index < total; index += 1) {
    onProgress(index + 1, index === 0 ? null : total);
    const data = await translatePart(uid, mailbox, locale, index);
    if (
      index > 0 &&
      (data.totalChunks !== total || data.sourceHash !== sourceHash)
    ) {
      throw new Error("Message changed during translation");
    }
    total = data.totalChunks;
    sourceHash = data.sourceHash;
    translated.push(data.translation.trim());
  }

  return translated.join("\n\n");
}

export function messageTranslationControl() {
  const body = document.querySelector("#message-content .rightcol");
  const headerLinks = document.querySelector("#message-header .header-links");
  const uid = String(rcmail.env.uid || "");
  const mailbox = rcmail.env.mailbox;
  const languages = rcmail.env.aiTranslationLanguages;
  if (
    !body ||
    !headerLinks ||
    !/^[1-9]\d*(?:\.\d+)*$/.test(uid) ||
    !mailbox ||
    !languages ||
    !Object.keys(languages).length
  )
    return;

  const trigger = document.createElement("a");
  trigger.href = "#";
  trigger.className = "aic-translation-trigger";
  trigger.setAttribute("role", "button");
  trigger.setAttribute("aria-haspopup", "dialog");
  trigger.setAttribute("aria-expanded", "false");
  trigger.setAttribute("aria-controls", "aic-translation-popover");
  trigger.title = label("ai_translate_mail", "Translate full email");
  const globe = document.createElementNS("http://www.w3.org/2000/svg", "svg");
  globe.setAttribute("viewBox", "0 0 24 24");
  globe.setAttribute("aria-hidden", "true");
  globe.setAttribute("focusable", "false");
  const outline = document.createElementNS(
    "http://www.w3.org/2000/svg",
    "path"
  );
  outline.setAttribute(
    "d",
    "M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-9 10h18M12 2c-3 2.7-4.5 6-4.5 10s1.5 7.3 4.5 10M12 2c3 2.7 4.5 6 4.5 10s-1.5 7.3-4.5 10"
  );
  globe.append(outline);
  const triggerText = document.createElement("span");
  triggerText.textContent = label("ai_translate_menu", "Translate");
  trigger.append(globe, triggerText);
  headerLinks.append(trigger);

  const popover = document.createElement("div");
  popover.id = "aic-translation-popover";
  popover.className = "aic-translation-popover";
  popover.setAttribute("role", "dialog");
  popover.setAttribute(
    "aria-label",
    label("ai_translate_mail", "Translate full email")
  );
  popover.hidden = true;
  const popoverHeader = document.createElement("div");
  popoverHeader.className = "aic-translation-popover-header";
  const title = document.createElement("strong");
  title.textContent = label("ai_translate_mail", "Translate full email");
  const closeButton = document.createElement("button");
  closeButton.type = "button";
  closeButton.className = "aic-translation-close";
  closeButton.textContent = "×";
  closeButton.setAttribute("aria-label", rcmail.gettext("close"));
  popoverHeader.append(title, closeButton);
  const targetLabel = document.createElement("label");
  targetLabel.htmlFor = "aic-translation-target";
  targetLabel.textContent = label("ai_translate_to", "Translate to");
  const target = document.createElement("select");
  target.id = "aic-translation-target";
  target.className = "form-control custom-select pretty-select";
  const collator = new Intl.Collator(
    document.documentElement.lang || undefined,
    {
      sensitivity: "base",
    }
  );
  const sortedLanguages = Object.entries(languages).sort(
    ([firstLocale, firstName], [secondLocale, secondName]) =>
      collator.compare(firstName, secondName) ||
      collator.compare(firstLocale, secondLocale)
  );
  for (const [locale, name] of sortedLanguages) {
    const option = document.createElement("option");
    option.value = locale;
    option.textContent = name;
    target.append(option);
  }
  if (
    Object.prototype.hasOwnProperty.call(
      languages,
      rcmail.env.aiTranslationDefaultLocale
    )
  ) {
    target.value = rcmail.env.aiTranslationDefaultLocale;
  }
  const translateButton = document.createElement("button");
  translateButton.type = "button";
  translateButton.className = "btn btn-primary aic-translation-submit";
  const description = document.createElement("p");
  description.className = "aic-translation-description";
  description.textContent = label(
    "ai_translation_description",
    "The original message stays below."
  );
  popover.append(
    popoverHeader,
    targetLabel,
    target,
    translateButton,
    description
  );
  document.body.append(popover);

  const resultPanel = document.createElement("section");
  resultPanel.className = "aic-translation-result-panel";
  resultPanel.setAttribute(
    "aria-label",
    label("ai_translation_result", "Translation")
  );
  resultPanel.hidden = true;
  const resultHeader = document.createElement("div");
  resultHeader.className = "aic-translation-result-header";
  const resultTitle = document.createElement("strong");
  const hideButton = document.createElement("button");
  hideButton.type = "button";
  hideButton.textContent = label("ai_translation_hide", "Hide translation");
  resultHeader.append(resultTitle, hideButton);
  const result = document.createElement("div");
  result.className = "aic-translation-result";
  resultPanel.append(resultHeader, result);

  const status = document.createElement("p");
  status.className = "aic-translation-status";
  status.setAttribute("role", "status");
  status.setAttribute("aria-live", "polite");
  let translatedLocale = null;
  let translatedText = null;

  function placeAboveMessage(element) {
    const anchor =
      body.querySelector(".aic-reply-suggestions") ||
      body.querySelector(".aic-summary-card");
    if (anchor) anchor.after(element);
    else body.prepend(element);
  }

  function updateActionLabel() {
    const hiddenResult =
      translatedText !== null &&
      translatedLocale === target.value &&
      resultPanel.hidden;
    translateButton.textContent =
      translatedText !== null && translatedLocale === target.value
        ? resultPanel.hidden
          ? label("ai_translation_show", "Show translation")
          : label("ai_translate_again_mail", "Translate again")
        : label("ai_translate_mail", "Translate full email");
    trigger.title = hiddenResult
      ? label("ai_translation_show", "Show translation")
      : label("ai_translate_mail", "Translate full email");
  }

  function positionPopover() {
    const rect = trigger.getBoundingClientRect();
    const width = popover.offsetWidth;
    const height = popover.offsetHeight;
    popover.style.left = `${Math.max(8, Math.min(rect.left, window.innerWidth - width - 8))}px`;
    const preferredTop =
      rect.bottom + height + 8 > window.innerHeight && rect.top >= height + 8
        ? rect.top - height - 6
        : rect.bottom + 6;
    popover.style.top = `${Math.max(8, Math.min(preferredTop, window.innerHeight - height - 8))}px`;
  }

  function closePopover(focusTrigger = false) {
    popover.hidden = true;
    trigger.setAttribute("aria-expanded", "false");
    if (focusTrigger && trigger.getAttribute("aria-disabled") !== "true")
      trigger.focus();
  }

  trigger.addEventListener("click", (event) => {
    event.preventDefault();
    if (trigger.getAttribute("aria-disabled") === "true") return;
    if (!popover.hidden) {
      closePopover();
      return;
    }
    popover.hidden = false;
    trigger.setAttribute("aria-expanded", "true");
    positionPopover();
    target.focus();
  });
  trigger.addEventListener("keydown", (event) => {
    if (event.key === " ") {
      event.preventDefault();
      trigger.click();
    }
  });
  closeButton.addEventListener("click", () => closePopover(true));
  document.addEventListener("pointerdown", (event) => {
    if (
      !popover.hidden &&
      !popover.contains(event.target) &&
      event.target !== trigger &&
      !trigger.contains(event.target)
    ) {
      closePopover();
    }
  });
  document.addEventListener("focusin", (event) => {
    if (
      !popover.hidden &&
      !popover.contains(event.target) &&
      event.target !== trigger
    ) {
      closePopover();
    }
  });
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !popover.hidden) {
      closePopover(true);
      event.preventDefault();
    }
  });
  document.addEventListener(
    "scroll",
    (event) => {
      if (!popover.hidden && !popover.contains(event.target)) closePopover();
    },
    true
  );
  window.addEventListener("resize", () => {
    if (!popover.hidden) positionPopover();
  });

  target.addEventListener("change", () => {
    if (translatedLocale !== target.value) {
      translatedLocale = null;
      translatedText = null;
      result.textContent = "";
      resultPanel.hidden = true;
    }
    updateActionLabel();
  });
  hideButton.addEventListener("click", () => {
    resultPanel.hidden = true;
    updateActionLabel();
    trigger.focus();
  });
  translateButton.addEventListener("click", async () => {
    if (
      translatedText !== null &&
      translatedLocale === target.value &&
      resultPanel.hidden
    ) {
      resultPanel.hidden = false;
      closePopover(true);
      updateActionLabel();
      return;
    }
    const requestedLocale = target.value;
    closePopover();
    trigger.setAttribute("aria-disabled", "true");
    trigger.tabIndex = -1;
    translateButton.disabled = true;
    target.disabled = true;
    hideButton.disabled = true;
    placeAboveMessage(status);
    try {
      const translated = await translateAllParts(
        uid,
        mailbox,
        requestedLocale,
        (index, total) => {
          status.textContent = label(
            "ai_translation_progress",
            "Translating part %1 of %2"
          )
            .replace("%1", String(index))
            .replace("%2", total === null ? "…" : String(total));
        }
      );
      translatedLocale = requestedLocale;
      translatedText = translated;
      result.textContent = translated;
      resultTitle.textContent = `${label("ai_translation_result", "Translation")} · ${target.selectedOptions[0].textContent}`;
      placeAboveMessage(resultPanel);
      resultPanel.hidden = false;
      updateActionLabel();
    } catch (error) {
      rcmail.display_message(
        error?.code === "too_large"
          ? label(
              "ai_translation_too_long",
              "This message is too long to translate in full."
            )
          : label(
              "ai_translation_error",
              "Could not translate this message. Please try again."
            ),
        "error"
      );
    } finally {
      status.remove();
      trigger.removeAttribute("aria-disabled");
      trigger.removeAttribute("tabindex");
      translateButton.disabled = false;
      target.disabled = false;
      hideButton.disabled = false;
    }
  });
  updateActionLabel();
}
