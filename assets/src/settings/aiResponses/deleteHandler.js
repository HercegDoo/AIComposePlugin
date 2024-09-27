import { getPredefinedMessages } from "./getMessagesHandler";

export function handleDelete(id){
const deleteButton = document.getElementById('delete-button');
deleteButton.classList.remove('disabled');
console.log("u handle delete");


deleteButton.addEventListener('click', (e)=>{
  e.preventDefault();
  console.log("delete klik");
    rcmail.http_post('plugin.aicdeletemessage', {id:`${id}`}).done( function(data) {
      console.log(data);
      rcmail.display_message("Uspjesno obrisano", 'confirmation');
      getPredefinedMessages();
    });
  })


}
