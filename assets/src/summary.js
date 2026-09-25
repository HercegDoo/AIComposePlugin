import "./summary/styles.css";

const action = "plugin.aicomposeplugin_SummarizeMessageAction";
const results = new Map();
const pending = new Map();

function label(key, fallback) {
  const text = rcmail.get_label(`aicomposeplugin.${key}`);
  return text === `aicomposeplugin.${key}` ? fallback : text;
}

function summarize(uid, mailbox, view = "preview", refresh = false) {
  const key = `${mailbox}\0${uid}\0${view}`;
  if (!refresh && results.has(key)) return Promise.resolve(results.get(key));
  if (!refresh && pending.has(key)) return pending.get(key);

  const request = new Promise((resolve, reject) => {
    rcmail
      .http_post(action, { uid, mailbox, view, refresh: refresh ? "1" : "0" })
      .done((data) => {
        if (data && data.status === "success") {
          results.set(key, data);
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

  const card = document.createElement("section");
  card.className = "aic-summary-card";
  card.setAttribute("aria-label", label("ai_summary", "AI summary"));
  const head = document.createElement("div");
  head.className = "aic-summary-head";
  const title = document.createElement("strong");
  title.textContent = label("ai_summary", "AI summary");
  const controls = document.createElement("span");
  const originalButton = document.createElement("button");
  originalButton.type = "button";
  originalButton.hidden = true;
  originalButton.textContent = label("ai_show_original", "Show original");
  const refreshButton = document.createElement("button");
  refreshButton.type = "button";
  refreshButton.textContent = label("ai_translate_again", "Translate again");
  controls.append(originalButton, refreshButton);
  head.append(title, controls);
  const summary = document.createElement("p");
  summary.textContent = label("ai_summary_loading", "Summarizing…");
  const original = document.createElement("p");
  original.className = "aic-summary-original";
  original.hidden = true;
  card.append(head, summary, original);
  body.prepend(card);

  let current;
  function render(data) {
    current = data;
    summary.textContent = data.translatedSummary;
    original.textContent = `${label("ai_original_language", "Original")} (${data.sourceLanguage}): ${data.originalSummary}`;
    originalButton.hidden = false;
    refreshButton.disabled = false;
  }
  function load(refresh) {
    refreshButton.disabled = true;
    summary.textContent = label("ai_summary_loading", "Summarizing…");
    summarize(uid, mailbox, "message", refresh)
      .then(render)
      .catch(() => {
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
  preview.hidden = true;
  document.body.append(preview);

  let timer;
  let activeRow;
  function hide() {
    clearTimeout(timer);
    activeRow = null;
    preview.hidden = true;
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
      const rect = row.getBoundingClientRect();
      preview.style.top = `${Math.max(8, Math.min(rect.bottom + 4, window.innerHeight - 110))}px`;
      preview.style.left = `${Math.max(8, Math.min(rect.left + 16, window.innerWidth - 350))}px`;
      preview.textContent = label("ai_summary_loading", "Summarizing…");
      preview.hidden = false;
      summarize(uid, mailbox)
        .then((data) => {
          if (activeRow === row) preview.textContent = data.translatedSummary;
        })
        .catch(() => {
          if (activeRow === row)
            preview.textContent = label(
              "ai_summary_error",
              "Summary unavailable. Try again."
            );
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
  messageCard();
  hoverPreview();
});
