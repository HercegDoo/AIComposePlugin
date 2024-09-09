
export function showRequestData() {
        const generateEmailButton = document.getElementById('generate-email-button');
        const senderNameElement  = document.getElementById('sender-name');
    const recipientNameElement  = document.getElementById('recipient-name');
    const styleElement = document.getElementById('aic-style');
    const lengthElement = document.getElementById('aic-length');
    const creativityElement = document.getElementById('aic-creativity');
    const languageElement = document.getElementById('aic-language');



     generateEmailButton.addEventListener('click', ()=>{
         const requestData = {
             senderName : `${senderNameElement.value}`,
             recipientName : `${recipientNameElement.value}`,
             style: `${styleElement.value}`,
             length: `${lengthElement.value}`,
             creativity: `${creativityElement.value}`,
             language: `${languageElement.value}`,
         }
         console.log(requestData)
     })


}