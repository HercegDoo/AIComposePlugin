export function getSenderInfo(){

    const iconLink = document.querySelector('a.iconlink.input-group-text');
    let senderInfo;
    let senderInfoElement;

    if(iconLink){
        if(iconLink.classList.contains('custom-from-on')){
             senderInfoElement = document.getElementById('_from');
             senderInfo = senderInfoElement.options[senderInfoElement.selectedIndex].textContent;
        }
        else {
            senderInfoElement = document.querySelector('input.custom_from.form-control');
            senderInfo = senderInfoElement.value;
            }

  return senderInfo;
        }

}

export function processSenderData(senderInfo) {
    const senderNameInputField =  document.getElementById('sender-name');
    const match = senderInfo.match(/^(.+?)\s+(.+@.+)$/);
    let senderName;
    let senderEmail;

    if (match) {
        //Prvi slucaj: ime + email
        senderName = match[1].trim();
        let emailCandidate = match[2].trim();

        senderEmail = emailCandidate.replace(/[<>]/g, '').trim();
        senderNameInputField.value = senderName;
    } else if (senderInfo.includes('@')) {
        //Drugi slucaj: samo email
        senderEmail = senderInfo.replace(/[<>]/g, '').trim(); // Provjeri i ukloni < i >, te trimuj
    } else {
        // Treći slucaj: samo ime
        senderName = senderInfo.trim();
        senderNameInputField.value = senderName;
    }

   return {
        senderName: `${senderName}`,
        senderEmail: `${senderEmail}`
   }
}