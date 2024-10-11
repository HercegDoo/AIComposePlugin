import { translation } from "./utils";
import { getPredefinedInstructions } from "./settings/aiResponses/requests/getInstructionsHandler";

let previousInstruction = null;
document.addEventListener('DOMContentLoaded', ()=>{
  rcmail.enable_command('addinstructiontemplate', true);


  const table = document.querySelector('#responses-table tbody');

  table.addEventListener('click', (event)=>{
    if(event.target.tagName === 'TD'){
      unselectPreviousInstruction();
      selectInstruction(event.target.parentElement);
      rcmail.addinstructiontemplate(event.target.parentElement.id);
    }
  })

  const createButton = document.getElementById('create-button');
  createButton.addEventListener('click', ()=>{
   unselectPreviousInstruction();
  })
  
  const deleteButton = document.getElementById('delete-button');
  deleteButton.addEventListener('click', ()=>{
    let id;
    const tdElements = document.querySelectorAll('#responses-table tbody tr td');
    tdElements.forEach((tdElement)=>{
      if (tdElement.parentElement.classList.contains('selected')){
        id = tdElement.parentElement.id;
      }
    })
    displayPopup(id);
  })
})

rcmail.register_command('updateinstructionlist', rcube_webmail.prototype.updateinstructionlist);
rcmail.register_command('addinstructiontemplate', rcube_webmail.prototype.addinstructiontemplate);
rcmail.register_command('deleteinstruction', rcube_webmail.prototype.deleteinstruction);


rcube_webmail.prototype.updateinstructionlist = function(id, title)
{
  let found = false;

  const tdElements = document.querySelectorAll('#responses-table tbody tr td');
  tdElements.forEach((tdElement)=>{
    if (tdElement.parentElement.id === id){
     tdElement.textContent = title;
     found = true;
    }
  })

  if(!found){
    const tbody = document.querySelector('#responses-table tbody');
    const trow = document.createElement('tr');
    trow.id =  id;
    const td = document.createElement('td');
    td.textContent = title;
    td.className = "name";
    trow.append(td);
    selectInstruction(trow);
    tbody.append(trow);
  }

};


rcube_webmail.prototype.addinstructiontemplate = function(id = null)
{
  let win;
  if(id){
    rcmail.enable_command('deleteinstruction', true);
  }
  else{  rcmail.enable_command('deleteinstruction', false)}
  if (win = rcmail.get_frame_window(rcmail.env.contentframe)) {
    rcmail.location_href({_action: "plugin.AIComposePlugin_AddInstruction", _id:id, _framed: 1}, win, true);
  }
};


rcube_webmail.prototype.deleteinstruction = function(id)
{
  let win;
  const tbody = document.querySelector('#responses-table tbody');
  const instructionToRemove = document.getElementById(id);
  if (tbody.contains(instructionToRemove)) {
    tbody.removeChild(instructionToRemove);
  }
  if (win = rcmail.get_frame_window(rcmail.env.contentframe)) {

  }
};

function unselectPreviousInstruction(){
  if(previousInstruction){
    previousInstruction.classList.remove("selected");
    previousInstruction.classList.remove("focused");
  }
}

function selectInstruction(instruction){
  instruction.classList.add("selected");
  instruction.classList.add("focused");
  previousInstruction = instruction;

}

function displayPopup(id){

  let content = translation('ai_predefined_popup_body');
  let title = translation('ai_predefined_popup_title');

  const buttons = {
    [translation('ai_predefined_delete')]: function() {
      popup.remove();
      deleteInstructionPostRequest(id);
    },
   [translation('ai_predefined_cancel')]: function() {
      popup.remove();

    }
  };

  const options = {
    width: 500,
    height: 45,
    modal: true,
    resizable: true,
    button_classes: ['mainaction delete btn btn-primary btn-danger', 'cancel btn btn-secondary']
  };

const popup = rcmail.show_popup_dialog(content, title, buttons, options);

}


function deleteInstructionPostRequest(id){
  rcmail.http_post("plugin.AIComposePlugin_DeleteInstruction", {_id: `${id}`}, true).done(function(data){
    rcmail.show_contentframe(false);
    rcmail.enable_command('deleteinstruction', false);
  });
}


