import "./summary/styles.css";
import { messageTranslationControl } from "./summary/translation";

const action = "plugin.aicomposeplugin_SummarizeMessageAction";
const results = new Map();
const pending = new Map();

function label(key, fallback) {
  const text = rcmail.get_label(`aicomposeplugin.${key}`);
  return text === `aicomposeplugin.${key}` ? fallback : text;
}

function icon(kind) {
  const mark = document.createElement("span");
  mark.className = `aic-summary-icon aic-summary-icon-${kind}`;
  mark.setAttribute("aria-hidden", "true");
  const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
  svg.setAttribute("viewBox", "0 0 24 24");
  svg.setAttribute("focusable", "false");
  const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
  path.setAttribute(
    "d",
    kind === "reply"
      ? "M4 5h16v11H9l-5 4V5Zm3 4h10m-10 3h7"
      : "M12 2.5 14 8l5.5 2-5.5 2-2 5.5-2-5.5-5.5-2L10 8l2-5.5ZM19 16l.6 1.4L21 18l-1.4.6L19 20l-.6-1.4L17 18l1.4-.6L19 16Z"
  );
  svg.append(path);
  mark.append(svg);
  return mark;
}

function summarize(uid, mailbox, view = "preview", refresh = false) {
  const key = `${mailbox}\0${uid}\0${view}`;
  if (!refresh && results.has(key)) return Promise.resolve(results.get(key));
  if (!refresh && pending.has(key)) return pending.get(key);

  const request = new Promise((resolve, reject) => {
    rcmail
      .http_post(action, { uid, mailbox, view, refresh: refresh ? "1" : "0" })
      .done((data) => {
        if (data && (data.status === "success" || data.status === "skipped")) {
          if (data.status === "success") results.set(key, data);
          resolve(data);
        } else {
          reject(new Error("Summary unavailable"));
        }
      })
      .fail(reject);
  });
  pending.set(key, request);
  request.finally(() => pending.delete(key)).catch(() => {});
  return request;
}

function messageCard() {
  const body = document.querySelector("#message-content .rightcol");
  const uid = String(rcmail.env.uid || "");
  const mailbox = rcmail.env.mailbox;
  if (!body || !/^[1-9]\d*(?:\.\d+)*$/.test(uid) || !mailbox) return;
  const onlyLong = rcmail.env.aiSummaryMessageMode === "long";

  const card = document.createElement("section");
  card.className = "aic-summary-card";
  card.setAttribute("aria-label", label("ai_summary", "AI summary"));
  const head = document.createElement("div");
  head.className = "aic-summary-head";
  const heading = document.createElement("div");
  heading.className = "aic-summary-heading";
  const title = document.createElement("strong");
  title.textContent = label("ai_summary", "AI summary");
  heading.append(icon("sparkles"), title);
  const controls = document.createElement("div");
  controls.className = "aic-summary-controls";
  const originalButton = document.createElement("button");
  originalButton.type = "button";
  originalButton.hidden = true;
  originalButton.textContent = label("ai_show_original", "Show original");
  const refreshButton = document.createElement("button");
  refreshButton.type = "button";
  refreshButton.textContent = label("ai_translate_again", "Translate again");
  controls.append(originalButton, refreshButton);
  head.append(heading, controls);
  const summary = document.createElement("p");
  summary.textContent = label("ai_summary_loading", "Summarizing…");
  const original = document.createElement("p");
  original.className = "aic-summary-original";
  original.hidden = true;
  const suggestions = document.createElement("section");
  suggestions.className = "aic-reply-suggestions";
  suggestions.setAttribute(
    "aria-label",
    label("ai_reply_suggestions", "Suggested replies")
  );
  suggestions.hidden = true;
  const suggestionsHeading = document.createElement("div");
  suggestionsHeading.className = "aic-reply-suggestions-heading";
  const suggestionsTitle = document.createElement("strong");
  suggestionsTitle.textContent = label(
    "ai_reply_suggestions",
    "Suggested replies"
  );
  suggestionsHeading.append(icon("reply"), suggestionsTitle);
  const suggestionsList = document.createElement("div");
  suggestionsList.className = "aic-reply-suggestion-list";
  suggestions.append(suggestionsHeading, suggestionsList);
  card.append(head, summary, original);
  let inserted = false;
  function showCard() {
    if (inserted) return;
    body.prepend(card, suggestions);
    inserted = true;
  }
  if (!onlyLong) showCard();

  let current;
  function prepareReply(item) {
    const buttons = suggestionsList.querySelectorAll("button");
    buttons.forEach((button) => (button.disabled = true));
    rcmail
      .http_post("plugin.aicomposeplugin_PrepareSuggestedReplyAction", {
        uid,
        mailbox,
        instruction: item.instruction,
        language: current.sourceLanguage,
      })
      .done((data) => {
        if (data?.status === "success" && /^[a-f0-9]{32}$/.test(data.token)) {
          rcmail.open_compose_step({
            _reply_uid: uid,
            _mbox: mailbox,
            _aic_reply_token: data.token,
          });
        } else {
          rcmail.display_message(
            label("ai_reply_suggestion_error", "Could not start the reply."),
            "error"
          );
        }
      })
      .fail(() => {
        rcmail.display_message(
          label("ai_reply_suggestion_error", "Could not start the reply."),
          "error"
        );
      })
      .always(() => buttons.forEach((button) => (button.disabled = false)));
  }
  function render(data) {
    current = data;
    summary.textContent = data.translatedSummary;
    original.textContent = `${label("ai_original_language", "Original")} (${data.sourceLanguage}): ${data.originalSummary}`;
    originalButton.hidden = data.translationEnabled === false;
    refreshButton.textContent =
      data.translationEnabled === false
        ? label("ai_summarize_again", "Summarize again")
        : label("ai_translate_again", "Translate again");
    refreshButton.disabled = false;
    suggestionsList.replaceChildren();
    const items = Array.isArray(data.replySuggestions)
      ? data.replySuggestions.slice(0, 3)
      : [];
    items.forEach((item) => {
      if (
        !item ||
        typeof item.label !== "string" ||
        typeof item.instruction !== "string"
      )
        return;
      const button = document.createElement("button");
      button.type = "button";
      button.textContent = item.label;
      button.addEventListener("click", () => prepareReply(item));
      suggestionsList.append(button);
    });
    suggestions.hidden = !suggestionsList.childElementCount;
  }
  function load(refresh) {
    refreshButton.disabled = true;
    summary.textContent = label("ai_summary_loading", "Summarizing…");
    suggestions.hidden = true;
    summarize(uid, mailbox, "message", refresh)
      .then((data) => {
        if (data.status === "skipped") {
          card.remove();
          suggestions.remove();
          inserted = false;
          return;
        }
        showCard();
        render(data);
      })
      .catch(() => {
        if (onlyLong && !inserted) return;
        summary.textContent = label(
          "ai_summary_error",
          "Summary unavailable. Try again."
        );
        refreshButton.disabled = false;
      });
  }
  originalButton.addEventListener("click", () => {
    if (!current) return;
    original.hidden = !original.hidden;
    originalButton.textContent = original.hidden
      ? label("ai_show_original", "Show original")
      : label("ai_hide_original", "Hide original");
  });
  refreshButton.addEventListener("click", () => load(true));
  load(false);
}

function hoverPreview() {
  const list = document.getElementById("messagelist");
  if (!list) return;
  const preview = document.createElement("div");
  preview.className = "aic-summary-preview";
  preview.setAttribute("role", "status");
  const previewHeading = document.createElement("div");
  previewHeading.className = "aic-summary-preview-heading";
  const previewTitle = document.createElement("strong");
  previewTitle.textContent = label("ai_summary", "AI summary");
  previewHeading.append(icon("sparkles"), previewTitle);
  const previewText = document.createElement("p");
  preview.append(previewHeading, previewText);
  preview.hidden = true;
  document.body.append(preview);

  let timer;
  let activeRow;
  function hide() {
    clearTimeout(timer);
    activeRow = null;
    preview.hidden = true;
  }
  function position(row) {
    const rect = row.getBoundingClientRect();
    const popup = preview.getBoundingClientRect();
    let top = rect.bottom + 8;
    if (top + popup.height > window.innerHeight - 8) {
      top = rect.top - popup.height - 8;
    }
    preview.style.top = `${Math.max(8, top)}px`;
    preview.style.left = `${Math.max(8, Math.min(rect.left + 16, window.innerWidth - popup.width - 8))}px`;
  }
  list.addEventListener("mouseover", (event) => {
    const row = event.target.closest("tr");
    if (!row || !list.contains(row) || row === activeRow) return;
    hide();
    const uid = String(row.uid || "");
    const mailbox = rcmail.env.mailbox;
    if (!/^[1-9]\d*(?:\.\d+)*$/.test(uid) || !mailbox) return;
    activeRow = row;
    timer = setTimeout(() => {
      if (activeRow !== row) return;
      previewText.textContent = label("ai_summary_loading", "Summarizing…");
      preview.hidden = false;
      position(row);
      summarize(uid, mailbox)
        .then((data) => {
          if (activeRow === row) {
            previewText.textContent = data.translatedSummary;
            position(row);
          }
        })
        .catch(() => {
          if (activeRow === row) {
            previewText.textContent = label(
              "ai_summary_error",
              "Summary unavailable. Try again."
            );
            position(row);
          }
        });
    }, 350);
  });
  list.addEventListener("mouseout", (event) => {
    if (
      activeRow &&
      event.target.closest("tr") === activeRow &&
      !activeRow.contains(event.relatedTarget)
    )
      hide();
  });
  list.addEventListener("scroll", hide, true);
  window.addEventListener("blur", hide);
}

document.addEventListener("DOMContentLoaded", () => {
  if (rcmail.env.aiSummaryEnabled === true) {
    if (rcmail.env.aiSummaryViews?.message !== false) messageCard();
    if (rcmail.env.aiSummaryViews?.preview !== false) hoverPreview();
  }
  if (rcmail.env.aiTranslationEnabled === true) messageTranslationControl();
});
