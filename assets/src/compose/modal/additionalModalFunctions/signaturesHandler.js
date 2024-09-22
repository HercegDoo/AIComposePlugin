import { getPreviousConversation } from "./getPreviousConversation";

let editorHTML = "";
let formattedSignatureText= "";
let formattedPreviousConversationText = "";
export function detectSignature(){
const textareaContent = document.getElementById('composebody').value;
console.log(textareaContent);
// const potentialSignatureFormatted = potentialSignature.replace(/\\n/g, '\n');

  if(editorHTML.editorContainer){
console.log("editor aktivan");
 const signature = rcmail.env.signatures['1']['text'];
const editorText = editorHTML.getContent({format: 'text'});
 formattedSignatureText = removeEmptyLinesAndSpaces(signature).replace(/\s+/g, ' ').trim();
 formattedPreviousConversationText = removeEmptyLinesAndSpaces(editorText).replace(/\s+/g, ' ').trim();
}
else{
  console.log("editor neaktivan");
  const textareaContentFormatted = textareaContent.replace(/\\n/g, '\n').trim();
  const signatureText = rcmail.env.signatures['1']['text'].trim();
  formattedSignatureText = removeEmptyLinesAndSpaces(signatureText);
  formattedPreviousConversationText = removeEmptyLinesAndSpaces(textareaContentFormatted);
}


if(formattedPreviousConversationText.includes(formattedSignatureText)){
  console.log(removeSubstring(formattedPreviousConversationText, formattedSignatureText))
  return removeSubstring(formattedPreviousConversationText, formattedSignatureText);
}
else{
  console.log(formattedPreviousConversationText);
  return formattedPreviousConversationText;
}
}

rcmail.addEventListener("editor-load", (e) => {
  editorHTML = e?.ref?.editor;
});

function removeEmptyLinesAndSpaces(text) {
  return text
    .split('\n') // Podelite tekst na redove
    .filter(line => line.trim() !== '') // Uklonite prazne redove
    .map(line => line.trim()) // Uklonite razmake sa početka i kraja
    .join('\n'); // Spojite redove nazad
}

function removeSubstring(originalString, substringToRemove) {
  return originalString.split(substringToRemove).join('');
}

