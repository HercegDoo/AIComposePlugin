let editorHTML = "";
let formattedPreviousConversationText = "";
export function signatureCheckedPreviousConversation() {
  let signaturesArray = [];
  const size = Object.keys(rcmail.env.signatures).length; // Broj potpisa

  for (let i = 0; i <= size; i++) {
    if (rcmail.env.signatures[i]) {
      const signatureText = rcmail.env.signatures[i]["text"];
      signaturesArray.push(signatureText);
    }
  }

  if (editorHTML.editorContainer) {
    let editorText = editorHTML.getContent({ format: "html" });
    const editorTextDiv = document.createElement("div");
    editorTextDiv.innerHTML = editorText;
    editorText = editorTextDiv.textContent;
    formattedPreviousConversationText = removeEmptyLinesAndSpaces(editorText)
      .replace(/\\n/g, "\n")
      .replace(/\s+/g, " ")
      .trim();
  } else {
    const textareaContent = document.getElementById("composebody").value;
    const textareaContentFormatted = textareaContent
      .replace(/\\n/g, "\n")
      .replace(/\s+/g, " ")
      .trim();
    formattedPreviousConversationText = removeEmptyLinesAndSpaces(
      textareaContentFormatted
    );
  }

  const formattedSignaturePresent = containsSubstring(
    formattedPreviousConversationText,
    signaturesArray
  );

  if (formattedSignaturePresent) {
    const startIndex = formattedPreviousConversationText.indexOf(
      formattedSignaturePresent
    );
    const endIndex = startIndex + formattedSignaturePresent.length;
    return {
      previousConversation: `${formattedPreviousConversationText.slice(0, startIndex) + formattedPreviousConversationText.slice(endIndex)}`,
      signaturePresent: "present",
    };
  } else {
    return {
      previousConversation: `${formattedPreviousConversationText}`,
      signaturePresent: "",
    };
  }
}

rcmail.addEventListener("editor-load", (e) => {
  editorHTML = e?.ref?.editor;
});

function removeEmptyLinesAndSpaces(text) {
  return text
    .split("\n")
    .filter((line) => line.trim() !== "")
    .map((line) => line.trim())
    .join("\n");
}

function containsSubstring(formattedPreviousConversationText, signaturesArray) {
  for (const [index, signature] of signaturesArray.entries()) {
    let formattedSignature;
    if (editorHTML.editorContainer) {
      const div = document.createElement("div");
      div.innerHTML = rcmail.env.signatures[`${String(index + 1)}`]["html"];
      formattedSignature = removeEmptyLinesAndSpaces(div.textContent)
        .replace(/\\n/g, "\n")
        .replace(/\s+/g, " ")
        .trim();
    } else {
      formattedSignature = removeEmptyLinesAndSpaces(signature)
        .replace(/\\n/g, "\n")
        .replace(/\s+/g, " ")
        .trim();
    }
    if (formattedPreviousConversationText.includes(formattedSignature)) {
      return formattedSignature;
    }
  }
  return null;
}
