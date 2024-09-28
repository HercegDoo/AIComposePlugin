import { getPredefinedMessages } from "./getMessagesHandler";

export function postMethodHandler(){
  const form = document.getElementById('form-post-add-edit');
  form.addEventListener('submit', (e)=>{
    e.preventDefault(); // Sprečava ponovno učitavanje stranice

let value= document.getElementById('aic-predefined-instructions-value-textarea').value;
let title = document.getElementById('aic-predefined-instructions-title-input').value;
const editMessageId = document.getElementById('edit-id').value;



    rcmail.http_post('plugin.aicresponsesrequest', {title: `${title}`, value: `${value}`, id:`${editMessageId}`}).done( function(data) {
       console.log(data);
     rcmail.display_message("Uspjesno sacuvano", 'confirmation');
       getPredefinedMessages();

  });
});
}