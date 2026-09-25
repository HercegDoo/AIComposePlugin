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

export function messageTranslationCard() {
  const body = document.querySelector("#message-content .rightcol");
  const uid = String(rcmail.env.uid || "");
  const mailbox = rcmail.env.mailbox;
  const languages = rcmail.env.aiTranslationLanguages;
  if (
    !body ||
    !/^[1-9]\d*(?:\.\d+)*$/.test(uid) ||
    !mailbox ||
    !languages ||
    !Object.keys(languages).length
  )
    return;

  const card = document.createElement("section");
  card.className = "aic-translation-card";
  card.setAttribute(
    "aria-label",
    label("ai_translate_mail", "Translate email")
  );
  const header = document.createElement("div");
  header.className = "aic-translation-header";
  const heading = document.createElement("div");
  heading.className = "aic-translation-heading";
  const symbol = document.createElement("span");
  symbol.className = "aic-translation-symbol";
  symbol.setAttribute("aria-hidden", "true");
  const globe = document.createElementNS("http://www.w3.org/2000/svg", "svg");
  globe.setAttribute("viewBox", "0 0 24 24");
  const outline = document.createElementNS(
    "http://www.w3.org/2000/svg",
    "path"
  );
  outline.setAttribute(
    "d",
    "M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-9 10h18M12 2c-3 2.7-4.5 6-4.5 10s1.5 7.3 4.5 10M12 2c3 2.7 4.5 6 4.5 10s-1.5 7.3-4.5 10"
  );
  globe.append(outline);
  symbol.append(globe);
  const title = document.createElement("strong");
  title.textContent = label("ai_translate_mail", "Translate email");
  heading.append(symbol, title);
  const description = document.createElement("p");
  description.textContent = label(
    "ai_translation_description",
    "Translate the full message. The original stays below."
  );
  header.append(heading, description);

  const controls = document.createElement("div");
  controls.className = "aic-translation-controls";
  const targetLabel = document.createElement("label");
  targetLabel.htmlFor = "aic-translation-target";
  targetLabel.textContent = label("ai_translate_to", "Translate to");
  const target = document.createElement("select");
  target.id = "aic-translation-target";
  target.className = "form-control custom-select pretty-select";
  for (const [locale, name] of Object.entries(languages)) {
    const option = document.createElement("option");
    option.value = locale;
    option.textContent = name;
    target.append(option);
  }
  target.value =
    rcmail.env.aiTranslationDefaultLocale || target.options[0].value;
  const translateButton = document.createElement("button");
  translateButton.type = "button";
  translateButton.className = "btn btn-primary";
  translateButton.textContent = label("ai_translate_mail", "Translate email");
  const toggleButton = document.createElement("button");
  toggleButton.type = "button";
  toggleButton.className = "btn btn-link";
  toggleButton.textContent = label("ai_translation_hide", "Hide translation");
  toggleButton.hidden = true;
  controls.append(targetLabel, target, translateButton, toggleButton);

  const status = document.createElement("p");
  status.className = "aic-translation-status";
  status.setAttribute("role", "status");
  status.setAttribute("aria-live", "polite");
  status.hidden = true;
  const result = document.createElement("div");
  result.className = "aic-translation-result";
  result.hidden = true;
  card.append(header, controls, status, result);
  const summary = body.querySelector(".aic-summary-card");
  if (summary) summary.after(card);
  else body.prepend(card);

  target.addEventListener("change", () => {
    result.hidden = true;
    result.textContent = "";
    toggleButton.hidden = true;
    status.hidden = true;
    translateButton.textContent = label("ai_translate_mail", "Translate email");
  });
  toggleButton.addEventListener("click", () => {
    result.hidden = !result.hidden;
    toggleButton.textContent = result.hidden
      ? label("ai_translation_show", "Show translation")
      : label("ai_translation_hide", "Hide translation");
  });
  translateButton.addEventListener("click", async () => {
    translateButton.disabled = true;
    target.disabled = true;
    toggleButton.hidden = true;
    result.hidden = true;
    status.hidden = false;
    try {
      result.textContent = await translateAllParts(
        uid,
        mailbox,
        target.value,
        (index, total) => {
          status.textContent = label(
            "ai_translation_progress",
            "Translating part %1 of %2"
          )
            .replace("%1", String(index))
            .replace("%2", total === null ? "…" : String(total));
        }
      );
      result.hidden = false;
      status.hidden = true;
      toggleButton.hidden = false;
      toggleButton.textContent = label(
        "ai_translation_hide",
        "Hide translation"
      );
      translateButton.textContent = label(
        "ai_translate_again_mail",
        "Translate again"
      );
    } catch (error) {
      result.textContent = "";
      status.textContent =
        error?.code === "too_large"
          ? label(
              "ai_translation_too_long",
              "This message is too long to translate in full."
            )
          : label(
              "ai_translation_error",
              "Could not translate this message. Please try again."
            );
    } finally {
      translateButton.disabled = false;
      target.disabled = false;
    }
  });
}
