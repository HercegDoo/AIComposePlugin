
import { getPredefinedMessages, getSpecificMessage } from "./getMessagesHandler";
import { handleDelete } from "./deleteHandler";
import { handleEdit } from "./editHandler";

export function messageClickHandle(){
  const layourMenuScrollbar = document.querySelector("#layout-list .scroller");

layourMenuScrollbar.addEventListener('click', (event)=>{
  const id = event.target.tagName.toLowerCase() === 'td' ? event.target.id: null;
  if(id){
    getSpecificMessage(id);
     handleDelete(id);
     handleEdit(id);
  }

})

}
