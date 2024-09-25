export function regulateCreateButton(){
  const createButton = document.querySelector('li[role="menuitem"] a.create');
  const iframeWrapper = document.querySelector('.iframe-wrapper');
  const formDiv = document.querySelector('#form-div');

  createButton.addEventListener('click', ()=>{
    console.log('klikeo');
   iframeWrapper.setAttribute('hidden', 'hidden');
   formDiv.removeAttribute('hidden');
  })

}