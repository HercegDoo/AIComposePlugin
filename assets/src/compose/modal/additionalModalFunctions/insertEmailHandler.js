import { setFilled } from "./predefinedInstructions/regulatePredefinedInstructionsModal";

let editorHTML;
let previousGeneratedEmail = "";
export function insertEmail(generatedEmail) {
  const insertEmailButton = document.getElementById("insert-email-button"),
   modalTextArea = document.getElementById("aic-email"),
   aiComposeModal = document.getElementById("aic-compose-dialog");

  if(aiComposeModal){
    insertEmailButton.addEventListener("click", () => {
      setFilled(false);
      document.body.removeChild(aiComposeModal);
     regulateInsertion(modalTextArea.value);
    });
  }
  else{
   regulateInsertion(generatedEmail);
  }

}

function regulateInsertion(emailToInsert) {
  const targetTextArea = document.getElementById("composebody");
  let formattedContent = emailToInsert;

  if (editorHTML) {
    formattedContent = emailToInsert.replace(/\n/g, "<br>");
    const content = tinymce.activeEditor.dom.decode(editorHTML.getContent()).replace(previousGeneratedEmail, "");
    editorHTML.setContent(`${formattedContent}${content}`);
    previousGeneratedEmail = `<p>${formattedContent.replace(/<br>/g, '<br />')}</p>`;
  } else {
    targetTextArea.value = targetTextArea.value.replace(previousGeneratedEmail, "");
    targetTextArea.value = `${emailToInsert}\n\n${targetTextArea.value}`;
    previousGeneratedEmail = emailToInsert;
  }
}


rcmail.addEventListener("editor-load", (e) => {
  editorHTML = e?.ref?.editor;
});

 export function getPreviousGeneratedInsertedEmail(){
  return previousGeneratedEmail;
}

