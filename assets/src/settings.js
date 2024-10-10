
document.addEventListener('DOMContentLoaded', ()=>{
  rcmail.enable_command('addinstructiontemplate', true);
  rcmail.enable_command('lol', true);

  const table = document.querySelector('#responses-table tbody');
  console.log(table);
  table.addEventListener('click', (event)=>{
    if(event.target.tagName === 'TD'){
      console.log(event.target.parentElement.id);
      rcmail.addinstructiontemplate(event.target.parentElement.id);
    }
  })
})

rcmail.register_command('updateinstructionlist', rcube_webmail.prototype.updateinstructionlist);
// rcmail.register_command('lol', rcube_webmail.prototype.lol);
rcmail.register_command('addinstructiontemplate', rcube_webmail.prototype.addinstructiontemplate);


rcube_webmail.prototype.updateinstructionlist = function(id, title)
{
  let found = false;

  console.log(document.querySelectorAll('#responses-table tbody tr td'));
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
    console.log("aaa");
    tbody.append(trow);
  }

};


rcube_webmail.prototype.addinstructiontemplate = function(id = null)
{
  let win;
  console.log(`Id iz addinstructiontemplate${id}`);
  console.log("pozvan");
  console.log(rcmail.env.action);
  if (win = rcmail.get_frame_window(rcmail.env.contentframe)) {
    console.log(rcmail.env.action);
    rcmail.location_href({_action: "plugin.AIComposePlugin_AddInstruction", _id:id, _framed: 1}, win, true);
    console.log(rcmail.env.action);
  }
};


