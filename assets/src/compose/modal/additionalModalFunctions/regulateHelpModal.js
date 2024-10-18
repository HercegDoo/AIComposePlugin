import { closeModal, openModal } from "../../../utils";


export function regulateHelpModal() {
  const instructionExampleBtn = document.getElementById("instruction-example"),
   request = document.getElementById("aic-result"),
   result = document.getElementById("aic-request"),
   helpContent = document.getElementById("aic-compose-help"),
   backBtn = document.getElementById("help-back-btn"),
   helpAs = document.querySelectorAll(".help-a"),
   instructionsTextarea = document.getElementById("aic-instructions");

  instructionExampleBtn?.addEventListener("click", (e) => {
    e.stopPropagation();
    openModal(request, result, helpContent);
  });

  backBtn?.addEventListener("click", (e) => {
    e.stopPropagation();
    closeModal(undefined, undefined, helpContent);
  });

  helpAs?.forEach((helpA) => {
    helpA.addEventListener("click", (event) => {
      event.stopPropagation();
      closeModal(undefined, undefined, helpContent);
      instructionsTextarea.value =
        event.target.previousElementSibling.innerText;
    });
  });

}
