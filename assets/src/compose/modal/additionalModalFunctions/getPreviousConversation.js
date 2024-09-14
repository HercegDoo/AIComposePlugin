let previousConversation = "";
let editorHTML;

export function getPreviousConversation() {
  if (editorHTML) {
    const value = editorHTML?.getContent({ format: "text" });

    if (value) {
      return value;
    }
  }

  //Uzimanje prethodnog maila ako postoji
  const previousConversationTextareaElement = document.querySelector(
    "#composebodycontainer textarea"
  );
  if (previousConversationTextareaElement.value.trim() !== "") {
    previousConversation = previousConversationTextareaElement.value.trim();
  } else previousConversation = "";

  return previousConversation;
}

rcmail.addEventListener("editor-load", (e) => {
  editorHTML = e?.ref?.editor;
});
