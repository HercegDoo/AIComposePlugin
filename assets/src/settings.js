
document.addEventListener('DOMContentLoaded', ()=>{
  rcmail.enable_command('add-instruction-template', true);
})

rcmail.register_command('updateinstructionlist', rcube_webmail.prototype.updateinstructionlist);
rcmail.register_command('add-instruction-template', function() {
  let win;

  if (win = rcmail.get_frame_window(rcmail.env.contentframe)) {
    rcmail.location_href({_action: "plugin.AIComposePlugin_AddInstruction", _id: 0, _framed: 1}, win, true);
  }
});


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
