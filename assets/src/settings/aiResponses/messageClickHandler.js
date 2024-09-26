import { getSpecificPredefinedMessage } from "./getSpecificPredefinedMessage";

export function messageClickHandle(){
  const createButton = document.querySelector('li[role="menuitem"] a.create');
  const iframeWrapper = document.querySelector('.iframe-wrapper');
  const formDiv = document.querySelector('#form-div');
  const messageTextArea = document.getElementById('aic-predefined-instructions-value-textarea');
  const titleInput = document.getElementById('aic-predefined-instructions-title-input');

  const layourMenuScrollbar = document.querySelector("#layout-list .scroller");

layourMenuScrollbar.addEventListener('click', (event)=>{
getSpecificPredefinedMessage(event.target.id);
})

}
