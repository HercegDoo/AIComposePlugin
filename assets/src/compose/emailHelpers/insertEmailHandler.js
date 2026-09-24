import {
  htmlToPlainText,
  plainTextToHtml,
  sanitizeHtmlMessage,
} from "./htmlEmail";

const generatedBlockId = "aic-generated-email";
let previousGeneratedEmailText = "";
let previousGeneratedEmailHtml = "";
let popupVisible = false;
let mailGenerated = false;
export function insertEmail(generatedEmail, responseIsHtml = false) {
  const htmlEditor = rcmail.editor?.is_html() ? rcmail.editor.editor : null;

  if (htmlEditor) {
    const formattedContent = responseIsHtml
      ? sanitizeHtmlMessage(generatedEmail)
      : plainTextToHtml(generatedEmail);
    if (!htmlToPlainText(formattedContent)) {
      return;
    }

    const body = htmlEditor.getBody();

    htmlEditor.undoManager.transact(() => {
      body.querySelector(`#${generatedBlockId}`)?.remove();

      const generatedBlock = htmlEditor.getDoc().createElement("div");
      generatedBlock.id = generatedBlockId;
      generatedBlock.innerHTML = formattedContent;
      body.insertBefore(generatedBlock, body.firstChild);

      previousGeneratedEmailText = generatedBlock.textContent ?? "";
      previousGeneratedEmailHtml = generatedBlock.innerHTML;
    });

    htmlEditor.nodeChanged();
    htmlEditor.save();
    htmlEditor.setDirty(true);
  } else {
    const targetTextArea = document.getElementById("composebody");
    const text = responseIsHtml
      ? htmlToPlainText(generatedEmail)
      : generatedEmail;
    if (!text.trim()) {
      return;
    }

    const oldText = previousGeneratedEmailHtml
      ? htmlToPlainText(previousGeneratedEmailHtml)
      : previousGeneratedEmailText;

    if (oldText) {
      targetTextArea.value = targetTextArea.value.replace(oldText, "");
    }

    const existingContent = targetTextArea.value;
    const separator =
      existingContent && !existingContent.startsWith("\n") ? "\n\n" : "";
    targetTextArea.value = `${text}${separator}${existingContent}`;
    previousGeneratedEmailText = text;
    previousGeneratedEmailHtml = "";
  }

  if (!mailGenerated) {
    popupVisible = true;
    mailGenerated = true;
  }
}

export function getPreviousGeneratedInsertedEmail(format = "text") {
  return format === "html"
    ? previousGeneratedEmailHtml
    : previousGeneratedEmailText;
}

export function popupCanBeVisible() {
  return popupVisible;
}
