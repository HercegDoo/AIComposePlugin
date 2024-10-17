import { sendPostRequest } from "./additionalModalFunctions/requests/sendPostRequest";
import { translation } from "../../utils";

export  function addDropdownToAIButton(){
  const dataDiv = document.createElement('div');
  dataDiv.id = "predefinedInstructionsMenu";
  dataDiv.classList.add('popupmenu');
  dataDiv.style.display = "none";

  const predefinedInstructions = rcmail.env.aiPredefinedInstructions;

  dataDiv.innerHTML = `
  
  <h3 class="voice">Canned instructions menu</h3>
\t<ul class="menu listing" role="menu">
\t\t<li role="separator" class="separator"><label>${translation('ai_predefined_use_instructions')}</label></li>
\t\t<ul id="instructionslist" class="rounded-0"></ul>
\t\t<li role="separator" class="separator"><label>${translation('ai_predefined_manage_instructions')}</label></li>
\t\t<li role="menuitem"><a class="edit responses active" onclick="return rcmail.command('switch-task', 'settings/plugin.basepredefinedinstructions')" role="button" href="#">${translation('ai_predefined_edit_instructions')}</a></li>
\t</ul>
    </div>
  `

  document.body.append(dataDiv);


  const instructionsList = document.getElementById('instructionslist');

  predefinedInstructions.forEach((predefinedInstruction) => {
    const liInstruction = document.createElement('li');
    const aInstruction = document.createElement('a');
    aInstruction.classList.add('insertresponse');
    aInstruction.textContent = predefinedInstruction.title;
    aInstruction.onclick  = function(){ return rcmail.command('generateEmail', predefinedInstruction.message);}
    liInstruction.append(aInstruction);
    instructionsList.append(liInstruction);
  });


  UI.popup_init(document.getElementById('instructionsdropdownlink'));

  rcmail.enable_command('generateEmail', true);

  rcmail.register_command('generateEmail', rcube_webmail.prototype.deleteinstruction);


  rcube_webmail.prototype.generateEmail = function(message) {
    sendPostRequest("", message);
  }
}