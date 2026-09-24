import { formatText } from "../../utils";
import { findPlainSignature, stripGeneratedClosing } from "./signatureUtils.mjs";

export function signatureCheckedPreviousConversation(previousGeneratedEmail = "") {
  let conversation;
  let signaturePresent = false;
  let signatureText = "";
  const editorHTML = rcmail.editor?.is_html() ? rcmail.editor.editor : null;

  if (editorHTML && editorHTML.getBody()) {
    const liveSignature = editorHTML.getBody().querySelector("#_rc_sig");
    const body = editorHTML.getBody().cloneNode(true);
    const signature = body.querySelector("#_rc_sig");

    if (
      signature &&
      (signature.textContent.trim() || signature.querySelector("img, svg"))
    ) {
      signaturePresent = true;
      signatureText = liveSignature.innerText || signature.textContent;
      signature.remove();
    }

    conversation = body.textContent;
  } else {
    conversation = document.getElementById("composebody").value.replace(/\r\n/g, "\n");
    const currentSignature = rcmail.env.signatures?.[rcmail.env.identity]?.text;
    const range = findPlainSignature(conversation, currentSignature);

    if (range) {
      signaturePresent = true;
      signatureText = currentSignature;
      conversation =
        conversation.slice(0, range.start) + conversation.slice(range.end);
    }
  }

  conversation = formatText(conversation).replace(previousGeneratedEmail, "");

  return {
    previousConversation: conversation.trim(),
    signaturePresent: signaturePresent ? "present" : "",
    signatureText,
  };
}

export { stripGeneratedClosing };
