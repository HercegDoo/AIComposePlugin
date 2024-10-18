import { closeModal, createElementWithId, translation } from "../../../../utils";
import { sendPostRequest } from "../requests/sendPostRequest";

export function displayModalContent(array) {
  const predefinedContent = document.querySelector(
    ".predefined-instructions-content"
  ),
    predefinedInstructionsUl = createElementWithId("ul", "predefined-instruction-ul"),
    noInstructionsDiv = createElementWithId("div", "no-instructions-div"),
    instructionsTextarea = document.getElementById("aic-instructions");

  if (array.length > 0) {
    array.forEach((instruction, index) => {
      const titleLi = createElementWithId("li", `predefined-title-li${index}`);
      const titleSpan = createElementWithId("span", `predefined-title-span${index}`);
      const titleA = createElementWithId("a", `predefined-title-a${index}`);
      const instructionLi = createElementWithId("li", `predefined-instruction-li${index}`);
      instructionLi.setAttribute("hidden", "hidden");

      titleSpan.textContent = instruction.title;
      instructionLi.textContent = instruction.message;

      titleA.textContent = translation("ai_help_use_example");
      titleA.classList.add("predefined-a");
      titleA.setAttribute("href", "javascript:void(0)");
      titleA.addEventListener("click", (event) => {
        event.stopPropagation();
        closeModal();
        const titleAParent = titleA.parentElement;
        instructionsTextarea.value =
          titleAParent.nextElementSibling.textContent;
        sendPostRequest();
      });

      titleLi.append(titleSpan, titleA);
      predefinedInstructionsUl.append(titleLi);
      predefinedInstructionsUl.append(instructionLi);
      noInstructionsDiv.setAttribute("hidden", "hidden");
    });
  } else {
    noInstructionsDiv.textContent = translation(
      "ai_predefined_no_instructions"
    );
    predefinedInstructionsUl.setAttribute("hidden", "hidden");
  }

  predefinedContent.append(noInstructionsDiv);
  predefinedContent.append(predefinedInstructionsUl);
}


