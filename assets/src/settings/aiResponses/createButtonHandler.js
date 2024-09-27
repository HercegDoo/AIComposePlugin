export function regulateCreateButton(){
  const createButton = document.querySelector('li[role="menuitem"] a.create');
  const iframeWrapper = document.querySelector('.iframe-wrapper');
  const formDiv = document.querySelector('#form-div');

  createButton.addEventListener('click', ()=>{
    console.log('klikeo');
    iframeWrapper.setAttribute('hidden', 'hidden');
    formDiv.removeAttribute('hidden');
    const messageTextArea = document.getElementById('aic-predefined-instructions-value-textarea');
    const titleInput = document.getElementById('aic-predefined-instructions-title-input');
    messageTextArea.value = "";
    titleInput.value = "";
    const deleteButton = document.getElementById('delete-button');
    deleteButton.classList.add('disabled');
    const hiddenInput = document.getElementById("hidden-input");
    hiddenInput.setAttribute("value", `${rcmail.env.request_token}`);
    const submit = document.getElementById('responses-submit');
    submit.addEventListener('click', (e)=>{
      e.stopPropagation();
    })
  })

}