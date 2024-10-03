
document.addEventListener('DOMContentLoaded', ()=>{
  // rcmail.enable_command('add', true);
  const createButton = document.querySelector('a.create');
  createButton.addEventListener('click', ()=>{
  rcmail.location_href({_action: "plugin.AIComposePlugin_AddInstruction", _id: 0 , _framed: 1}, rcmail.get_frame_window(rcmail.env.contentframe), true)
    const ffname = document.getElementById('ffname');
  const fftext = document.getElementById('fftext');
    document.querySelector('.formbuttons .btn.btn-primary.submit').addEventListener('click', ()=>{
      rcmail.http_post('plugin.AIComposePlugin_SaveInstruction', {_name: `${ffname.value}`, _text:`${fftext.value}`}).done(function(data){
        console.log(data);
      });
    })
  })

})



