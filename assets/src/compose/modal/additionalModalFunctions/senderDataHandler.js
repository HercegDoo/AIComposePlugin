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
    // Promijenjeni regex koji traži ime i email
    const senderNameInputField =  document.getElementById('sender-name');
    const match = senderInfo.match(/^(.+?)\s+(.+@.+)$/);
    let senderName;
    let sendereEmail;

    if (match) {
        senderName = match[1].trim();
        let emailCandidate = match[2].trim();

        sendereEmail = emailCandidate.replace(/[<>]/g, '').trim();
        senderNameInputField.value = senderName;
    } else if (senderInfo.includes('@')) {
        sendereEmail = senderInfo.replace(/[<>]/g, '').trim(); // Provjeri i ukloni < i >, te trimuj
    } else {
        // Treći slučaj: "Harun"
        senderName = senderInfo.trim();
        senderNameInputField.value = senderName;
    }

   return {
        senderName: `${senderName}`,
        senderEmail: `${sendereEmail}`
   }
}