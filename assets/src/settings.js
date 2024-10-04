
document.addEventListener('DOMContentLoaded', ()=>{

  rcube_webmail.prototype.konj = function(){
    console.log(66);
  }
  rcmail.enable_command('konj', true);

rcmail.register_command('print', ()=>{
  console.log("print");
}, true)
  const createButton = document.querySelector('a.create');
  createButton.addEventListener('click', ()=>{
  rcmail.location_href({_action: "plugin.AIComposePlugin_AddInstruction", _id: 0 , _framed: 1}, rcmail.get_frame_window(rcmail.env.contentframe), true)

  })

})



