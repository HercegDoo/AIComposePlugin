import { sendPostRequest } from "./modal/additionalModalFunctions/requests/sendPostRequest";
import { insertEmail } from "./modal/additionalModalFunctions/insertEmailHandler";

export const createPredefinedInstructionsButtonInToolbarMenu = function () {
  const parentMenu = document.getElementById("toolbar-menu");
  const liElement1 = document.createElement("li");
  liElement1.id = "aic-predefined-instructions-button-li";
  liElement1.setAttribute("role", "menuitem");
  parentMenu.append(liElement1);

document.addEventListener('DOMContentLoaded', ()=>{
  rcmail.enable_command('test', true);
})


  const dataDiv = document.createElement('div');
  dataDiv.id = "menu1";
  dataDiv.classList.add('popupmenu');
  dataDiv.style.display = "none";


  const predefinedInstructions = rcmail.env.aiPredefinedInstructions;
  console.log(predefinedInstructions);

  dataDiv.innerHTML = `
    <div class="popover-header">Naslov Menija</div>
  <h3 class="voice">Canned instructions menu</h3>
\t<ul class="menu listing" role="menu">
\t\t<li role="separator" class="separator"><label>Insert instructions</label></li>
\t\t<ul id="instructionslist" class="rounded-0"></ul>
\t\t<li role="separator" class="separator"><label>Manage instructions</label></li>
\t\t<li role="menuitem"><a class="edit responses active" onclick="return rcmail.command('switch-task', 'settings/plugin.basepredefinedinstructions')" id="rcmbtn114" role="button" href="#">Edit instructions</a></li>
\t</ul>
    </div>
  `

  document.body.append(dataDiv);


  const instructionsList = document.getElementById('instructionslist');
  console.log(instructionsList);

  predefinedInstructions.forEach((predefinedInstruction) => {
    console.log(predefinedInstruction);
    const liInstruction = document.createElement('li');
    const aInstruction = document.createElement('a');
    aInstruction.classList.add('insertresponse');
    aInstruction.textContent = predefinedInstruction.title;
    aInstruction.onclick  = function(){ return rcmail.command('test', predefinedInstruction.message);}
    liInstruction.append(aInstruction);
    instructionsList.append(liInstruction);
  });

  liElement1.innerHTML = `
<a href=# id="btnu" class="responses active"   data-popup="menu1"  data-original-title="Insert a response"><span class="inner">Responses</span></a>`;


  UI.popup_init(document.getElementById('btnu'));

  rcmail.enable_command('test', true);

  rcmail.register_command('test', rcube_webmail.prototype.deleteinstruction);


  rcube_webmail.prototype.test = function(message) {
    sendPostRequest("", message);
    console.log(44);
  }
}
