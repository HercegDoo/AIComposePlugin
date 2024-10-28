let editorHTML = "";
let formattedPreviousConversationText = "";

export function signatureCheckedPreviousConversation(previousGeneratedEmail = "") {



  if (editorHTML.editorContainer) {
    let editorText = editorHTML.getContent({ format: "html" });
    const editorTextDiv = document.createElement("div");
    editorTextDiv.innerHTML = editorText;
    editorText = editorTextDiv.textContent;
   previousGeneratedEmail =  previousGeneratedEmail.replace(/\n/g, '').replace(/\s{2,}/g, '');
editorText = editorText.replace(previousGeneratedEmail, "");
    formattedPreviousConversationText = removeEmptyLinesAndSpaces(editorText)
      .replace(/\\n/g, "\n")
      .replace(/\s+/g, " ")
      .trim();
  } else {
    const textareaContent = document.getElementById("composebody").value.replace(previousGeneratedEmail, "");
    const textareaContentFormatted = textareaContent
      .replace(/\\n/g, "\n")
      .replace(/\s+/g, " ")
      .trim();
    formattedPreviousConversationText = removeEmptyLinesAndSpaces(
      textareaContentFormatted
    );
  }

  const formattedSignaturePresent = containsSubstring(
    formattedPreviousConversationText
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

function containsSubstring(formattedPreviousConversationText) {
  for (const key of Object.keys(rcmail.env.signatures)) {
    let formattedSignature;
    if (editorHTML.editorContainer) {
      const div = document.createElement("div");
      div.innerHTML = rcmail.env.signatures[key]['html']; // Koristi samo signature bez indeksa

      formattedSignature = removeEmptyLinesAndSpaces(div.textContent)
        .replace(/\\n/g, "\n")
        .replace(/\s+/g, " ")
        .trim();
    } else {
      formattedSignature = removeEmptyLinesAndSpaces(rcmail.env.signatures[key]['text'])
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