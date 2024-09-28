import { hideDeleteButton, showInputFields } from "./displayHandler";

export function regulateCreateButton(){
  const createButton = document.querySelector('li[role="menuitem"] a.create');


  createButton.addEventListener('click', ()=>{
   showInputFields();
    hideDeleteButton();
    const hiddenInput = document.getElementById("hidden-input");
    hiddenInput.setAttribute("value", `${rcmail.env.request_token}`);
    const submit = document.getElementById('responses-submit');
    submit.addEventListener('click', (e)=>{
      e.stopPropagation();
    })
  })

}