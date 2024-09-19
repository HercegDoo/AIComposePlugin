import { getFormattedPreviousGeneratedEmail } from "./selectedTextHandler";
import { sendPostRequest } from "./sendPostRequest";


export function regulateFixTextModal() {
  const fixTextContent = document.getElementById("aic-fix-text-section");
  const fixSelectedTextBtn = document.getElementById("fix-selected-text");
  const request = document.getElementById("aic-result");
  const result = document.getElementById("aic-request");
  const backBtn = document.getElementById("fix-text-back-btn");
  const selectedTextTextarea = document.getElementById('selected-text');
  const previousGeneratedEmailTextarea = document.getElementById('aic-email');
  const generateAgain = document.getElementById('fix-text-generate-again');
  const fixInstructionsTextArea = document.getElementById('fix-instructions');


  fixSelectedTextBtn?.addEventListener("click", (e) => {
    e.stopPropagation();
    openFixTextModal();
  });

  backBtn?.addEventListener("click", (e) => {
    e.stopPropagation();
    closeFixTextModal();
  });


  function openFixTextModal() {
    request.setAttribute("hidden", "hidden");
    result.setAttribute("hidden", "hidden");
    fixTextContent.removeAttribute("hidden");
    selectedTextTextarea.innerHTML =  getFormattedPreviousGeneratedEmail();
    fixInstructionsTextArea.value = '';
  }

  function closeFixTextModal() {
    request.removeAttribute("hidden");
    result.removeAttribute("hidden");
    fixTextContent.setAttribute("hidden", "true");
  }

  generateAgain.addEventListener('click', ()=>{
   sendPostRequest(previousGeneratedEmailTextarea.value, fixInstructionsTextArea);
    closeFixTextModal();
  })
}