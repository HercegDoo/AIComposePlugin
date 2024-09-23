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
    // const signature = rcmail.env.signatures['1']['html'];
    // const div = document.createElement('div');
    // div.innerHTML = signature;
    // console.log(signaturesArray[0] === rcmail.env.signatures['1']['text'] );
    let editorText = editorHTML.getContent({format: 'html'});
    const div1 = document.createElement('div');
    div1.innerHTML = editorText;
    editorText = div1.textContent;
    // console.log("EDITOR TEXT");
    // console.log(editorText);
    // console.log("SIGNATURE TEXT");
    // console.log(div.textContent);
    // formattedSignatureText = removeEmptyLinesAndSpaces(signature).replace(/\s+/g, ' ').trim();
    // console.log("Formatiran potpis");
    // console.log(removeEmptyLinesAndSpaces(div.textContent).replace(/\\n/g, '\n').replace(/\s+/g, ' ').trim() );
    // console.log("Formatiran razgoor");
    formattedPreviousConversationText = removeEmptyLinesAndSpaces(editorText).replace(/\\n/g, '\n').replace(/\s+/g, ' ').trim();
    // console.log(formattedPreviousConversationText);
    // console.log(formattedPreviousConversationText.includes(removeEmptyLinesAndSpaces(div.textContent).replace(/\\n/g, '\n').replace(/\s+/g, ' ').trim()));
  }
  else{
    console.log("editor neaktivan");
    const textareaContentFormatted = textareaContent.replace(/\\n/g, '\n').replace(/\s+/g, ' ').trim();
    // const signatureText = rcmail.env.signatures['1']['text']
    // console.log(signaturesArray[1].trim() ===  signatureText)
    // formattedSignatureText = removeEmptyLinesAndSpaces(signatureText).replace(/\\n/g, '\n').replace(/\s+/g, ' ').trim();
    // console.log("Formatirani potpis");
    // console.log(formattedSignatureText);
    formattedPreviousConversationText = removeEmptyLinesAndSpaces(textareaContentFormatted);
    // console.log("Formatirani prethodni razg");
    // console.log(formattedPreviousConversationText);
    // console.log(formattedPreviousConversationText.includes(formattedSignatureText));
  }

  const formattedSignaturePresent = containsSubstring(formattedPreviousConversationText, signaturesArray);
  console.log(formattedSignaturePresent);
  console.log('Return');


  if(formattedSignaturePresent){
    console.log('Uso u present');
    // console.log(formattedPreviousConversationText);
    // console.log(formattedSignaturePresent);
    // console.log(formattedPreviousConversationText.includes(formattedSignaturePresent));
    // const fii = removeAllSubstrings(formattedPreviousConversationText, formattedSignaturePresent);
    // console.log(fii);
    // formattedPreviousConversationText.replace(formattedSignaturePresent, 'HALOOO');
    // console.log(formattedPreviousConversationText);
    // console.log(formattedPreviousConversationText.indexOf(formattedSignaturePresent));
    const startIndex = formattedPreviousConversationText.indexOf(formattedSignaturePresent);
    console.log(startIndex);
    const endIndex = startIndex + formattedSignaturePresent.length;
    console.log(endIndex);
    console.log(formattedPreviousConversationText.slice(0, startIndex) + formattedPreviousConversationText.slice(endIndex));
    // const endIndex = startIndex + formattedSignaturePresent.length;
    // const newText = formattedPreviousConversationText.slice(0, startIndex);
    // console.log(newText);
    // return removeAllSubstrings(formattedPreviousConversationText, formattedSignaturePresent);
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

// function removeAllSubstrings(originalString, substringToRemove) {
//  return originalString.replace(substringToRemove, '');
// }

function containsSubstring(formattedPreviousConversationText, signaturesArray) {
  for (const [index, signature] of signaturesArray.entries()) {
    let formattedSignature;
    if (editorHTML.editorContainer) {
      const div = document.createElement('div');
      div.innerHTML = rcmail.env.signatures[`${String(index + 1)}`]['html'];
      formattedSignature = removeEmptyLinesAndSpaces(div.textContent).replace(/\\n/g, '\n').replace(/\s+/g, ' ').trim();
    } else {
      formattedSignature = removeEmptyLinesAndSpaces(signature).replace(/\\n/g, '\n').replace(/\s+/g, ' ').trim();
    }

    // console.log(formattedPreviousConversationText);
    // console.log(formattedSignature);
    // console.log('Index:', index); // Prikazujemo indeks
    // console.log('ooo');

    if (formattedPreviousConversationText.includes(formattedSignature)) {
      // console.log('USO');
      return formattedSignature; // Vraća se i potpis i njegov indeks
    }
  }
  return null; // Ako nijedan potpis ne sadrži
}


