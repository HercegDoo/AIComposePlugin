import { getPreviousConversation } from "./getPreviousConversation";

let editorHTML = "";
let signaturesText= "";
let previousConversationText = "";
export function detectSignature(){
const potentialSignature = document.getElementById('composebody').textContent;
const potentialSignatureFormatted = potentialSignature.replace(/\\n/g, '\n');

if(editorHTML){
   const divEditorText = document.createElement('div');
   divEditorText.innerHTML = editorHTML.getContent({ format: "html" });

  const divSignaturesText = document.createElement('div');
   divSignaturesText.innerHTML = rcmail.env.signatures['1']['html'];

   signaturesText = divSignaturesText.innerText.replace(/\s+/g, ' ').trim();
   previousConversationText = divEditorText.innerText.replace(/\s+/g, ' ').trim();
}

else{
  console.log('Uso u else');
  signaturesText = rcmail.env.signatures['1']['text'].replace(/\s+/g, ' ').trim();
  previousConversationText = potentialSignatureFormatted.replace(/\s+/g, ' ').trim();
}

console.log(previousConversationText);
console.log(signaturesText);

console.log(previousConversationText.includes(signaturesText));

if(previousConversationText.includes(signaturesText)){
  console.log(`${signaturesText}--signaturue`);
  return signaturesText;
}

}

rcmail.addEventListener("editor-load", (e) => {
  editorHTML = e?.ref?.editor;
});