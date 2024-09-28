export function showInputFields(title = "", message = ""){
  const iframeWrapper = document.querySelector('.iframe-wrapper');
  const formDiv = document.querySelector('#form-div');
  iframeWrapper.setAttribute('hidden', 'hidden');
  formDiv.removeAttribute('hidden');
  document.getElementById('aic-predefined-instructions-value-textarea').value = message;
  document.getElementById('aic-predefined-instructions-title-input').value = title;
}

export  function hideDeleteButton(){
  const deleteButton = document.getElementById('delete-button');
  deleteButton.classList.add('disabled');
}

export function putInFocus(element){
  element.classList.add("selected");
  element.classList.add("focused");
}

export function removeFocus(element){
  element.classList.remove("selected");
  element.classList.remove("focused");
}

export function clearInputFields(){
  document.getElementById('aic-predefined-instructions-value-textarea').value = "";
  document.getElementById('aic-predefined-instructions-title-input').value = "";
}