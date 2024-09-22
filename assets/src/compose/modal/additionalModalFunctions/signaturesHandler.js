import { getPreviousConversation } from "./getPreviousConversation";
import form from "parsleyjs/src/parsley/form";

let editorHTML = "";
let formattedSignatureText= "";
let formattedPreviousConversationText = "";
export function detectSignature(){
const textareaContent = document.getElementById('composebody').value;
// console.log(textareaContent);
  let signaturesArray = [];
  const size = Object.keys(rcmail.env.signatures).length; // Broj potpisa

  for (let i = 0; i <= size; i++) {
    if (rcmail.env.signatures[i]) { // Provjerite da li postoji taj potpis
      const signatureText = rcmail.env.signatures[i]['text'];
      signaturesArray.push(signatureText);
    }
  }

// Sada signaturesArray sadrži sve tekstove potpisa
  console.log(signaturesArray);


  if(editorHTML.editorContainer){
console.log("editor aktivan");
//  const signature = rcmail.env.signatures['1']['text'];
// console.log(signaturesArray[0] === rcmail.env.signatures['1']['text'] );
const editorText = editorHTML.getContent({format: 'text'});
 // formattedSignatureText = removeEmptyLinesAndSpaces(signature).replace(/\s+/g, ' ').trim();
 // console.log(formattedSignatureText);
 formattedPreviousConversationText = removeEmptyLinesAndSpaces(editorText).replace(/\s+/g, ' ').trim();
 console.log(formattedPreviousConversationText);
}
else{
  console.log("editor neaktivan");
  const textareaContentFormatted = textareaContent.replace(/\\n/g, '\n').trim();
  // const signatureText = rcmail.env.signatures['2']['text']
  // console.log(signaturesArray[1].trim() ===  signatureText)
  // formattedSignatureText = removeEmptyLinesAndSpaces(signatureText).trim();
  // console.log(formattedSignatureText);
  formattedPreviousConversationText = removeEmptyLinesAndSpaces(textareaContentFormatted);
  console.log(formattedPreviousConversationText);
}

const formattedSignaturePresent = containsSubstring(formattedPreviousConversationText, signaturesArray);
console.log(formattedSignaturePresent);
console.log('Return');


if(formattedSignaturePresent){
  console.log('Uso u present');
  console.log(formattedPreviousConversationText);
  console.log(formattedSignaturePresent);
  console.log(formattedPreviousConversationText.includes(formattedSignaturePresent));
const fii = removeAllSubstrings(formattedPreviousConversationText, formattedSignaturePresent);
console.log(fii);
formattedPreviousConversationText.replace(formattedSignaturePresent, 'HALOOO');
console.log(formattedPreviousConversationText);
console.log(formattedPreviousConversationText.indexOf(formattedSignaturePresent));
const endIndex = 11 + formattedSignaturePresent.length;
console.log(formattedPreviousConversationText.slice(0, 11) + formatted );
  return removeAllSubstrings(formattedPreviousConversationText, formattedSignaturePresent);
}
else{
  // console.log(formattedPreviousConversationText);
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

function removeAllSubstrings(originalString, substringToRemove) {
  const regex = new RegExp(substringToRemove, 'g'); // 'g' označava globalno pretraživanje
  return originalString.replace(regex, '');
}

function containsSubstring(formattedPreviousConversationText, signaturesArray) {
  for (const signature of signaturesArray) {
    const formattedSignature = removeEmptyLinesAndSpaces(signature).trim();
    console.log(formattedPreviousConversationText);
    console.log(formattedSignature);
    if (formattedPreviousConversationText.includes(formattedSignature)) {
      console.log('USO');
      return formattedSignature; // Vraća se iz cele funkcije
    }
  }
  return null; // Ako nijedan potpis ne sadrži
}


