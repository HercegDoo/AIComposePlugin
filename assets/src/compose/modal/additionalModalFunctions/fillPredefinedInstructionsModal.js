import { clearInputFields, hideDeleteButton } from "../../../settings/aiResponses/displayHandler";
import { translation } from "../../../utils";
import { closeModal } from "./regulatePredefinedInstructionsModal";

export function fillPredefinedInstructionsModal(){
  const predefinedContent = document.querySelector('.predefined-instructions-content');
  const predefinedInstructionsUl = document.createElement('ul');
  predefinedInstructionsUl.id = "predefined-instruction-ul";
  const noInstructionsDiv = document.createElement('div');
  noInstructionsDiv.id = "no-instructions-div";

  const instructionsTextarea = document.getElementById("aic-instructions");


  rcmail.http_get('plugin.aicGetAllInstructions', {})
    .done(function(data) {
      if (data.status === 'success') {

        if(data.returnValue.length > 0){
          data.returnValue.forEach((instruction,index)=>{
            const titleLi = document.createElement('li');
            titleLi.id = `predefined-title-li${index}`;
            const titleSpan = document.createElement('span');
            titleSpan.id = `predefined-title-span${index}`;
            const titleA = document.createElement('a');
            titleA.id = `predefined-title-a${index}`

            const instructionLi = document.createElement('li');
            instructionLi.id = `predefined-instruction-li${index}`;
            instructionLi.setAttribute('hidden', 'hidden');

            titleSpan.textContent = instruction.title;
            instructionLi.textContent = instruction.value;

            titleA.textContent = translation("ai_help_use_example");
            titleA.classList.add("predefined-a");
            titleA.setAttribute('href', 'javascript:void(0)');


            titleLi.append(titleSpan, titleA);
            predefinedInstructionsUl.append(titleLi);
            predefinedInstructionsUl.append(instructionLi);
            noInstructionsDiv.setAttribute('hidden', 'hidden');

            const helpAs = document.querySelectorAll(".predefined-a");
            helpAs?.forEach((helpA) => {
              helpA.addEventListener("click", (event) => {
                console.log("click na a");
                event.stopPropagation();
                closeModal();
                instructionsTextarea.value =
                  event.target.previousElementSibling.innerText;
              });
            });

          })
        }
        else{
          noInstructionsDiv.textContent = "nemate instrukcija";
          predefinedInstructionsUl.setAttribute('hidden','hidden');
        }

        predefinedContent.append(noInstructionsDiv);
        predefinedContent.append(predefinedInstructionsUl);

      } else {
        rcmail.display_message(`${translation('ai_predefined_error')}: ${data.message}`, 'error');
      }
    })
    .fail(function() {
      rcmail.display_message(`${translation('ai_predefined_instructions_error')}`, 'error');
    })
}

