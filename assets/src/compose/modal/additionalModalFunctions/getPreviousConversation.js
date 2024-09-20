let previousConversation = "";
let editorHTML;

export function getPreviousConversation(textt = "") {
  if (editorHTML) {
    const value = editorHTML?.getContent({ format: "html" });

    if (value) {
      
      const div = document.createElement('div');
      div.innerHTML = value;
      const valueToReturn = div.innerText.replace(/\s+/g, ' ').trim();
      // console.log(`-----------------${valueToReturn.replace(new RegExp(textt, 'g'), '').replace(/\|/g, '')}-----------`);
      return valueToReturn.replace(new RegExp(textt, 'g'), '').replace(/\|/g, '');
      // return value.replace(/\s+/g, ' ').trim()
    }
  }

  //Uzimanje prethodnog maila ako postoji
  const previousConversationTextareaElement = document.querySelector(
    "#composebodycontainer textarea"
  );
  if (previousConversationTextareaElement.value.trim() !== "") {
    previousConversation = previousConversationTextareaElement.value.trim();
  } else previousConversation = "";

  const divv = document.createElement('div');
  divv.innerHTML = previousConversation;
  const textToReturn = divv.innerText.replace(/\s+/g, ' ').trim();
  // console.log(textt);
  // console.log(textToReturn.replace(new RegExp(textt, 'g'), ''));
  return textToReturn.replace(new RegExp(textt, 'g'), '').replace(/\|/g, '');
}

rcmail.addEventListener("editor-load", (e) => {
  editorHTML = e?.ref?.editor;
});
