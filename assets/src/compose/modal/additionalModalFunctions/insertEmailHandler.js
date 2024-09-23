let editorHTML;
export function insertEmail() {
  const insertEmailButton = document.getElementById("insert-email-button");
  const modalTextArea = document.getElementById("aic-email");
  const targetTextArea = document.getElementById("composebody");
  const aiComposeModal = document.getElementById("aic-compose-dialog");

  insertEmailButton.addEventListener("click", () => {
    const inserteeEmail = modalTextArea.value;
    document.body.removeChild(aiComposeModal);
    if (editorHTML) {
      const formattedContent = inserteeEmail.replace(/\n/g, "<br>");
      editorHTML.setContent(`${formattedContent + editorHTML.getContent()}`);
    } else {
      targetTextArea.value = inserteeEmail + "\n\n" + targetTextArea.value;
    }
  });
}

rcmail.addEventListener("editor-load", (e) => {
  editorHTML = e?.ref?.editor;
});
