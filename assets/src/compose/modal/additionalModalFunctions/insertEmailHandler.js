export function insertEmail(){
  const insertEmailButton = document.getElementById('insert-email-button');
  const modalTextArea = document.getElementById('aic-email');
  const targetTextArea = document.getElementById('composebody');
  const aiComposeModal = document.getElementById('aic-compose-dialog');


  insertEmailButton.addEventListener('click', ()=>{
    const inserteeEmail = modalTextArea.value;
    document.body.removeChild(aiComposeModal);
    targetTextArea.value += inserteeEmail;
  })

}