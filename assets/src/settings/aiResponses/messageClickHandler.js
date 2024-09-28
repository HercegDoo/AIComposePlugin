
import { getSpecificMessage } from "./getMessagesHandler";
import { handleDelete } from "./deleteHandler";
import { handleEdit } from "./editHandler";
import { putInFocus, removeFocus } from "./displayHandler";

let previousSelectedRow = null;
export function messageClickHandle(){
  const layourMenuScrollbar = document.querySelector("#layout-list .scroller");

layourMenuScrollbar.addEventListener('click', (event)=>{
  const id = event.target.tagName.toLowerCase() === 'td' ? event.target.id: null;
  if(id){

    if(previousSelectedRow){
      removeFocus(previousSelectedRow);
    }

    getSpecificMessage(id);
     handleDelete(id);
     handleEdit(id);

     const currentRow =  event.target.parentElement;
     putInFocus(currentRow);

    previousSelectedRow = currentRow;
  }
})

}
