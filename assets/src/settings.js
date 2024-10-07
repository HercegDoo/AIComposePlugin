
document.addEventListener('DOMContentLoaded', ()=>{

  const createButton = document.querySelector('a.create');
  createButton.addEventListener('click', ()=>{
  rcmail.location_href({_action: "plugin.AIComposePlugin_AddInstruction", _id: 0 , _framed: 1}, rcmail.get_frame_window(rcmail.env.contentframe), true)
  })

})



