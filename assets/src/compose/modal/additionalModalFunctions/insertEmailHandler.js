let editorHTML;
let previousGeneratedEmail = "";
export function insertEmail(generatedEmail) {
  const insertEmailButton = document.getElementById("insert-email-button");
  const modalTextArea = document.getElementById("aic-email");
  const aiComposeModal = document.getElementById("aic-compose-dialog");

  if(aiComposeModal){
    insertEmailButton.addEventListener("click", () => {
      document.body.removeChild(aiComposeModal);
     regulateInsertion(modalTextArea.value);
    });
  }
  else{
   regulateInsertion(generatedEmail);
  }

}

function regulateInsertion(emailToInsert){
  const targetTextArea = document.getElementById("composebody");
  if (editorHTML) {
    const formattedContent = emailToInsert.replace(/\n/g, "<br>");
    let content = tinymce.activeEditor.dom.decode(editorHTML.getContent());
    content = content.replace(previousGeneratedEmail, "");
    editorHTML.setContent(`${formattedContent + content}`);

    previousGeneratedEmail = formattedContent;
    previousGeneratedEmail = `<p>${previousGeneratedEmail}</p>`;
    previousGeneratedEmail = previousGeneratedEmail.replace(/<br>/g, '<br />');
  } else {
    targetTextArea.value = targetTextArea.value.replace(previousGeneratedEmail, "");
    targetTextArea.value = emailToInsert + "\n\n" + targetTextArea.value;
    previousGeneratedEmail = emailToInsert;
  }
}

rcmail.addEventListener("editor-load", (e) => {
  editorHTML = e?.ref?.editor;
});
