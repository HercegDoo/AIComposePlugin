import { translation } from "../../../../utils";
import { closeModal } from "./regulatePredefinedInstructionsModal";

export function displayModalContent(array) {
  const predefinedContent = document.querySelector(
    ".predefined-instructions-content"
  );
  const predefinedInstructionsUl = document.createElement("ul");
  predefinedInstructionsUl.id = "predefined-instruction-ul";
  const noInstructionsDiv = document.createElement("div");
  noInstructionsDiv.id = "no-instructions-div";
  const instructionsTextarea = document.getElementById("aic-instructions");

  if (array.length > 0) {
    array.forEach((instruction, index) => {
      const titleLi = document.createElement("li");
      titleLi.id = `predefined-title-li${index}`;
      const titleSpan = document.createElement("span");
      titleSpan.id = `predefined-title-span${index}`;
      const titleA = document.createElement("a");
      titleA.id = `predefined-title-a${index}`;

      const instructionLi = document.createElement("li");
      instructionLi.id = `predefined-instruction-li${index}`;
      instructionLi.setAttribute("hidden", "hidden");

      titleSpan.textContent = instruction.title;
      instructionLi.textContent = instruction.value;

      titleA.textContent = translation("ai_help_use_example");
      titleA.classList.add("predefined-a");
      titleA.setAttribute("href", "javascript:void(0)");
      titleA.addEventListener("click", () => {
        event.stopPropagation();
        closeModal();
        const titleAParent = titleA.parentElement;
        instructionsTextarea.value =
          titleAParent.nextElementSibling.textContent;
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
