export function getPreviousGeneratedEmail(){
  const previousGeneratedEmailTextarea = document.getElementById('aic-email');
  const fixSelectedTextButton = document.getElementById('fixSelectedText');
  let previousGenereatedEmail = "";

  fixSelectedTextButton.addEventListener('click', ()=>{
    previousGenereatedEmail = previousGeneratedEmailTextarea.value;
  })

  return previousGenereatedEmail;
}