import "./askMail/styles.css";

const askAction = "plugin.aicomposeplugin_AskMailAction";
const statusAction = "plugin.aicomposeplugin_AskMailStatusAction";

function label(key, fallback) {
  const value = rcmail.get_label(`aicomposeplugin.${key}`);
  return value === `aicomposeplugin.${key}` ? fallback : value;
}

function request(action, data) {
  return new Promise((resolve, reject) => {
    rcmail.http_post(action, data).done(resolve).fail(reject);
  });
}

function createElement(tag, className, content) {
  const element = document.createElement(tag);
  if (className) element.className = className;
  if (content) element.textContent = content;
  return element;
}

function init() {
  if (rcmail.env.aiAskMailEnabled !== true) return;
  const toolbar =
    document.querySelector("#toolbar-menu") ||
    document.querySelector("#layout-content > .header .toolbar") ||
    document.querySelector("#layout-content > .header");
  if (!toolbar || document.getElementById("aic-ask-mail-button")) return;

  const trigger = createElement(
    "button",
    "aic-ask-mail-trigger",
    label("ai_ask_mail", "Ask your mail")
  );
  trigger.id = "aic-ask-mail-button";
  trigger.type = "button";
  trigger.setAttribute("aria-haspopup", "dialog");
  const placement = toolbar.tagName === "UL" ? createElement("li") : toolbar;
  placement.append(trigger);
  if (placement !== toolbar) toolbar.append(placement);

  const backdrop = createElement("div", "aic-ask-backdrop");
  backdrop.hidden = true;
  const dialog = createElement("section", "aic-ask-dialog");
  dialog.setAttribute("role", "dialog");
  dialog.setAttribute("aria-modal", "true");
  dialog.setAttribute("aria-labelledby", "aic-ask-title");
  const heading = createElement("div", "aic-ask-heading");
  const title = createElement("h2", "", label("ai_ask_mail", "Ask your mail"));
  title.id = "aic-ask-title";
  const close = createElement("button", "aic-ask-close", "×");
  close.type = "button";
  close.setAttribute("aria-label", label("ai_ask_close", "Close"));
  heading.append(title, close);
  const description = createElement(
    "p",
    "aic-ask-description",
    label(
      "ai_ask_description",
      "Search your mail and get an answer with sources."
    )
  );
  const form = createElement("form", "aic-ask-form");
  const question = createElement("input", "aic-ask-input");
  question.type = "search";
  question.required = true;
  question.maxLength = 1000;
  question.placeholder = label(
    "ai_ask_placeholder",
    "What do you want to know?"
  );
  question.setAttribute("aria-label", question.placeholder);
  const submit = createElement(
    "button",
    "aic-ask-submit",
    label("ai_ask_submit", "Ask")
  );
  submit.type = "submit";
  form.append(question, submit);
  const progress = createElement("p", "aic-ask-progress");
  progress.setAttribute("role", "status");
  const feedback = createElement("p", "aic-ask-feedback");
  feedback.setAttribute("role", "status");
  feedback.setAttribute("aria-live", "polite");
  const answer = createElement("div", "aic-ask-answer");
  const sources = createElement("ol", "aic-ask-sources");
  dialog.append(
    heading,
    description,
    form,
    progress,
    feedback,
    answer,
    sources
  );
  backdrop.append(dialog);
  document.body.append(backdrop);

  function renderProgress(data) {
    if (!data || typeof data !== "object") return;
    const indexed = Number(data.indexed) || 0;
    const skipped = Number(data.skipped) || 0;
    const estimated = Number(data.estimated) || 0;
    if (estimated > 0) {
      progress.textContent = `${label("ai_ask_index_progress", "Indexed messages")}: ${indexed.toLocaleString()} / ${estimated.toLocaleString()}${data.complete ? "" : " …"}`;
    } else {
      progress.textContent = label(
        "ai_ask_index_waiting",
        "Waiting for the mail indexer…"
      );
    }
    if (skipped > 0) {
      progress.textContent += ` · ${label("ai_ask_skipped", "Skipped")}: ${skipped.toLocaleString()}`;
    }
    if (data.error) {
      progress.textContent += ` · ${label("ai_ask_index_error", "Indexer needs attention")}`;
    }
  }

  function renderSources(items, used) {
    sources.replaceChildren();
    if (!Array.isArray(items)) return;
    items.forEach((item) => {
      if (!item || !/^\d+(?:\.\d+)*$/.test(String(item.uid || ""))) return;
      const row = createElement("li", "aic-ask-source");
      const link = createElement(
        "a",
        "",
        `[${item.number}] ${item.subject || label("ai_ask_untitled", "No subject")}`
      );
      link.href = rcmail.url("show", { _uid: item.uid, _mbox: item.mailbox });
      const meta = createElement(
        "small",
        "",
        [item.mailbox, item.filename, item.date].filter(Boolean).join(" · ")
      );
      const excerpt = createElement("p", "", item.excerpt || "");
      row.append(link, meta, excerpt);
      if (Array.isArray(used) && used.includes(item.number)) {
        row.classList.add("aic-ask-cited");
      }
      sources.append(row);
    });
  }

  let timer;
  async function loadProgress() {
    try {
      const data = await request(statusAction, {});
      if (data?.status === "success") renderProgress(data.progress);
    } catch (_) {
      progress.textContent = label(
        "ai_ask_unavailable",
        "Search is unavailable."
      );
    }
  }
  function hide() {
    backdrop.hidden = true;
    clearInterval(timer);
    trigger.focus();
  }
  trigger.addEventListener("click", () => {
    backdrop.hidden = false;
    question.focus();
    loadProgress();
    clearInterval(timer);
    timer = setInterval(loadProgress, 30000);
  });
  close.addEventListener("click", hide);
  backdrop.addEventListener("click", (event) => {
    if (event.target === backdrop) hide();
  });
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !backdrop.hidden) hide();
  });
  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (!question.value.trim()) return;
    submit.disabled = true;
    answer.textContent = "";
    sources.replaceChildren();
    feedback.textContent = label("ai_ask_working", "Searching your mail…");
    try {
      const data = await request(askAction, {
        question: question.value.trim(),
      });
      if (["success", "empty", "sources"].includes(data?.status)) {
        renderProgress(data.progress);
        answer.textContent = data.answer || "";
        renderSources(data.sources, data.citations);
        feedback.textContent =
          data.status === "sources"
            ? label(
                "ai_ask_answer_unavailable",
                "Answer generation is unavailable; search results are shown below."
              )
            : data.status === "empty" || !data.answer
              ? label(
                  "ai_ask_no_answer",
                  "No supported answer found yet. Try another question or wait for indexing."
                )
              : data.mode === "lexical"
                ? label(
                    "ai_ask_lexical",
                    "Semantic search is unavailable; showing text search results."
                  )
                : "";
      } else {
        throw new Error("Ask Mail failed");
      }
    } catch (_) {
      feedback.textContent = label(
        "ai_ask_unavailable",
        "Search is unavailable. Try again later."
      );
    } finally {
      submit.disabled = false;
    }
  });
}

document.addEventListener("DOMContentLoaded", init);
