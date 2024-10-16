let editorHTML;
let previousGeneratedEmail = "";
export function insertEmail(generatedEmail) {
  const insertEmailButton = document.getElementById("insert-email-button");
  const modalTextArea = document.getElementById("aic-email");
  const targetTextArea = document.getElementById("composebody");
  const aiComposeModal = document.getElementById("aic-compose-dialog");

  if(aiComposeModal){
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
  else{
    const inserteeEmail = generatedEmail;
    if (editorHTML) {
      const formattedContent = inserteeEmail.replace(/\n/g, "<br>");
      let content = tinymce.activeEditor.dom.decode(editorHTML.getContent());
      content = content.replace(previousGeneratedEmail, "");
      editorHTML.setContent(`${formattedContent + content}`);

      previousGeneratedEmail = formattedContent;
      previousGeneratedEmail = `<p>${previousGeneratedEmail}</p>`;
      previousGeneratedEmail = previousGeneratedEmail.replace(/<br>/g, '<br />');
      console.log(previousGeneratedEmail);
    } else {
      targetTextArea.value = targetTextArea.value.replace(previousGeneratedEmail, "");
      targetTextArea.value = inserteeEmail + "\n\n" + targetTextArea.value;
      previousGeneratedEmail = inserteeEmail;

    }
  }

}

rcmail.addEventListener("editor-load", (e) => {
  editorHTML = e?.ref?.editor;
});
