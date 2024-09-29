import { fillPredefinedInstructionsModal } from "./fillPredefinedInstructionsModal";

export function regulatePredefinedInstructionsModal(){
  const request = document.getElementById("aic-result");
  const result = document.getElementById("aic-request");
  const predefinedInstructionsButton = document.getElementById('predefined-instructions-button');
  const predefinedInstructionsDiv = document.getElementById('aic-compose-predefined');
  const backBtn = document.getElementById("predefined-back-btn");
  const helpAs = document.querySelectorAll(".predefined-a");
  const instructionsTextarea = document.getElementById("aic-instructions");

  predefinedInstructionsButton.addEventListener('click', ()=>{
    openModal(request, result, predefinedInstructionsDiv);
    fillPredefinedInstructionsModal();
    console.log(helpAs);
  })

  backBtn?.addEventListener("click", (e) => {
    e.stopPropagation();
    closeModal(request, result, predefinedInstructionsDiv);
  });



}

function openModal(request, result, modal){
  request.setAttribute("hidden", "hidden");
  result.setAttribute("hidden", "hidden");
  modal.removeAttribute("hidden");
}

export function closeModal(request = document.getElementById("aic-result"),result = document.getElementById("aic-request"), modal = document.getElementById('aic-compose-predefined')) {
  request.removeAttribute("hidden");
  result.removeAttribute("hidden");
 modal.setAttribute("hidden", "true");
}