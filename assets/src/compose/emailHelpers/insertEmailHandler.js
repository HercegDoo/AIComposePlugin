
let editorHTML;
let previousGeneratedEmail = "";
let popupVisible = false;
let counter = 0;
export function insertEmail(generatedEmail) {

   regulateInsertion(generatedEmail);

}

function regulateInsertion(emailToInsert) {
  const targetTextArea = document.getElementById("composebody");
  let formattedContent = emailToInsert;

  if (editorHTML && tinymce.activeEditor) {
    formattedContent = emailToInsert.replace(/\n/g, "<br>");
    const content = tinymce?.activeEditor?.dom.decode(editorHTML.getContent()).replace(previousGeneratedEmail, "");
    editorHTML.setContent(`${formattedContent}${content}`);
    previousGeneratedEmail = `<p>${formattedContent.replace(/<br>/g, '<br />')}</p>`;
  } else {
    targetTextArea.value = targetTextArea.value.replace(previousGeneratedEmail, "");
    targetTextArea.value = `${emailToInsert}\n\n${targetTextArea.value}`;
    previousGeneratedEmail = emailToInsert;
  }
  if(counter === 0){
    popupVisible = true;
    ++counter;
  }
}


rcmail.addEventListener("editor-load", (e) => {
  editorHTML = e?.ref?.editor;
});

 export function getPreviousGeneratedInsertedEmail(){
  return previousGeneratedEmail;
}

export function popupCanBeVisible(){
   return popupVisible;
}
