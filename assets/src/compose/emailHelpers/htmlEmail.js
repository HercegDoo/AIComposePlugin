import { stripGeneratedClosing } from "./signatureUtils.mjs";

// Keep generated markup within the basic formatting supported by Roundcube's
// TinyMCE compose editor. Attributes other than safe link destinations are removed.
const allowedTags = new Set([
  "p",
  "div",
  "br",
  "strong",
  "em",
  "u",
  "ul",
  "ol",
  "li",
  "blockquote",
  "a",
  "h1",
  "h2",
  "h3",
  "table",
  "thead",
  "tbody",
  "tr",
  "th",
  "td",
]);

const discardedTags = new Set([
  "script",
  "style",
  "iframe",
  "object",
  "embed",
  "svg",
  "math",
  "img",
  "video",
  "audio",
  "form",
  "input",
  "textarea",
  "button",
  "select",
  "link",
  "meta",
  "template",
]);

const blockTags = new Set([
  "p",
  "div",
  "blockquote",
  "h1",
  "h2",
  "h3",
  "ul",
  "ol",
  "li",
  "table",
  "tr",
]);

export function plainTextToHtml(text) {
  const element = document.createElement("div");
  element.textContent = text;
  return element.innerHTML.replace(/\r?\n/g, "<br>");
}

function appendSafeNode(node, parent) {
  if (node.nodeType === Node.TEXT_NODE) {
    parent.appendChild(document.createTextNode(node.textContent));
    return;
  }

  if (node.nodeType !== Node.ELEMENT_NODE) {
    return;
  }

  const tag = node.tagName.toLowerCase();
  if (discardedTags.has(tag)) {
    return;
  }

  const normalizedTag = tag === "b" ? "strong" : tag === "i" ? "em" : tag;
  const target = allowedTags.has(normalizedTag)
    ? document.createElement(normalizedTag)
    : parent;
  if (tag === "a" && target !== parent) {
    const href = node.getAttribute("href")?.trim();
    if (href && /^(?:https?:\/\/|mailto:)/i.test(href)) {
      target.setAttribute("href", href);
    }
  }

  for (const child of node.childNodes) {
    appendSafeNode(child, target);
  }

  if (target !== parent) {
    parent.appendChild(target);
  }
}

export function sanitizeHtmlMessage(html) {
  const withoutFence = html
    .trim()
    .replace(/^```(?:html)?[ \t]*\r?\n/i, "")
    .replace(/\r?\n```$/, "");
  const template = document.createElement("template");
  template.innerHTML = withoutFence;
  const safe = document.createElement("div");

  for (const child of template.content.childNodes) {
    appendSafeNode(child, safe);
  }

  // Preserve line breaks if the model returned plain text despite the HTML prompt.
  return safe.children.length
    ? safe.innerHTML
    : plainTextToHtml(safe.textContent);
}

function visibleText(node) {
  if (node.nodeType === Node.TEXT_NODE) {
    return node.textContent;
  }

  if (node.nodeType !== Node.ELEMENT_NODE) {
    return "";
  }

  const tag = node.tagName.toLowerCase();
  if (tag === "br") {
    return "\n";
  }

  const content = Array.from(node.childNodes).map(visibleText).join("");
  if (tag === "li") {
    return `- ${content.trim()}\n`;
  }

  return blockTags.has(tag) ? `${content}\n` : content;
}

export function htmlToPlainText(html) {
  const safe = sanitizeHtmlMessage(html);
  const template = document.createElement("template");
  template.innerHTML = safe;
  return Array.from(template.content.childNodes)
    .map(visibleText)
    .join("")
    .replace(/[ \t]+\n/g, "\n")
    .replace(/\n{3,}/g, "\n\n")
    .trim();
}

export function stripGeneratedHtmlClosing(html, senderName, signatureText) {
  const safe = sanitizeHtmlMessage(html);
  const originalText = htmlToPlainText(safe);
  const withoutClosing = stripGeneratedClosing(
    originalText,
    senderName,
    signatureText
  );

  if (!withoutClosing || withoutClosing === originalText) {
    return safe;
  }

  const container = document.createElement("div");
  container.innerHTML = safe;
  let candidate = container.lastElementChild;
  while (candidate) {
    const parent = candidate.parentElement;
    candidate.remove();

    const remainingText = htmlToPlainText(container.innerHTML);
    if (!remainingText.startsWith(withoutClosing)) {
      parent.appendChild(candidate);
      candidate = candidate.lastElementChild;
      continue;
    }

    if (remainingText === withoutClosing) {
      break;
    }

    candidate = container.lastElementChild;
  }

  return sanitizeHtmlMessage(container.innerHTML);
}
