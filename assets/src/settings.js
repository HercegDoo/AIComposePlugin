
document.addEventListener('DOMContentLoaded', ()=>{
  rcmail.enable_command('addinstructiontemplate', true);
  rcmail.enable_command('lol', true);
})

rcmail.register_command('updateinstructionlist', rcube_webmail.prototype.updateinstructionlist);
// rcmail.register_command('lol', rcube_webmail.prototype.lol);
rcmail.register_command('addinstructiontemplate', rcube_webmail.prototype.addinstructiontemplate);


rcube_webmail.prototype.updateinstructionlist = function(id, title)
{
  const tbody = document.querySelector('#responses-table tbody');
  console.log(document.querySelector('#responses-table tbody'));
  const trow = document.createElement('tr');
  trow.id = "rcmrow" + id;
  const td = document.createElement('td');
  td.textContent = title;
  td.className = "name";
  trow.append(td);
  console.log("aaa");
  tbody.append(trow);
};


rcube_webmail.prototype.addinstructiontemplate = function()
{
  let win;
  console.log("pozvan");
  console.log(rcmail.env.action);
  if (win = rcmail.get_frame_window(rcmail.env.contentframe)) {
    console.log(rcmail.env.action);
    rcmail.location_href({_action: "plugin.AIComposePlugin_AddInstruction", _id: 0, _framed: 1}, win, true);
    console.log(rcmail.env.action);
  }
};
