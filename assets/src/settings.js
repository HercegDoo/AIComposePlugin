let previousInstruction = null;
document.addEventListener('DOMContentLoaded', ()=>{
  rcmail.enable_command('addinstructiontemplate', true);
  rcmail.enable_command('lol', true);

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
})

rcmail.register_command('updateinstructionlist', rcube_webmail.prototype.updateinstructionlist);
rcmail.register_command('addinstructiontemplate', rcube_webmail.prototype.addinstructiontemplate);


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
  if (win = rcmail.get_frame_window(rcmail.env.contentframe)) {
    rcmail.location_href({_action: "plugin.AIComposePlugin_AddInstruction", _id:id, _framed: 1}, win, true);
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




