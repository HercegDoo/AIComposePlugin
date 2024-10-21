import { getFormattedPreviousGeneratedEmail, getSelectedText } from "./selectedTextHandler";
import { sendPostRequest } from "./requests/sendPostRequest";
import { closeModal } from "../../../utils";


export function regulateFixTextModal() {
  const fixTextContent = document.getElementById("aic-fix-text-section"),
   fixSelectedTextBtn = document.getElementById("fix-selected-text"),
   request = document.getElementById("aic-result"),
   result = document.getElementById("aic-request"),
   backBtn = document.getElementById("fix-text-back-btn"),
   selectedTextTextarea = document.getElementById("selected-text"),
   previousGeneratedEmailTextarea = document.getElementById("aic-email"),
   generateAgain = document.getElementById("fix-text-generate-again"),
   fixInstructionsTextArea = document.getElementById("fix-instructions");

  fixSelectedTextBtn?.addEventListener("click", (e) => {
    e.stopPropagation();
    openFixTextModal();
  });

  backBtn?.addEventListener("click", (e) => {
    e.stopPropagation();
    closeModal(undefined, undefined,  fixTextContent );
  });

  function openFixTextModal() {
    request.setAttribute("hidden", "hidden");
    result.setAttribute("hidden", "hidden");
    fixTextContent.removeAttribute("hidden");
    selectedTextTextarea.innerHTML = getFormattedPreviousGeneratedEmail();
    fixInstructionsTextArea.value = "";
  }


  generateAgain.addEventListener("click", () => {
    sendPostRequest(
      previousGeneratedEmailTextarea.value,
      fixInstructionsTextArea.value,
      getSelectedText()
    );
    closeModal(undefined, undefined,  fixTextContent );
  });
}
