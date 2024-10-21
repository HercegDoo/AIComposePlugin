
import { displayModalContent } from "./displayModalContent";
import { closeModal, openModal } from "../../../../utils";
let filled = false;

export function setFilled(value){
  filled = value;
}

export function regulatePredefinedInstructionsModal() {
  const request = document.getElementById("aic-result"),
   result = document.getElementById("aic-request"),
   predefinedInstructionsButton = document.getElementById(
    "predefined-instructions-button"
  ),
   predefinedInstructionsDiv = document.getElementById(
    "aic-compose-predefined"
  ),
  backBtn = document.getElementById("predefined-back-btn");

  predefinedInstructionsButton.addEventListener("click", () => {
    openModal(request, result, predefinedInstructionsDiv);
    if (!filled) {
     displayModalContent(rcmail.env.aiPredefinedInstructions);
      filled = true;
    }
  });

  backBtn?.addEventListener("click", (e) => {
    e.stopPropagation();
    closeModal(request, result, predefinedInstructionsDiv);
  });
}


