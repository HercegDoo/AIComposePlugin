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
    const senderNameInputField = document.getElementById('sender-name');
    let senderName = '';
    let senderEmail = '';

    // Regex za razdvajanje imena i emaila
    const match = senderInfo.match(/^(.+?)\s+<(.+?)>$/);

    if (match) {
        // Prvi slučaj: ime + email u formatu "Ime <email>"
        senderName = match[1].trim();
        let emailCandidate = match[2].trim();
        senderEmail = emailCandidate.replace(/[<>]/g, '').trim();
        senderNameInputField.value = senderName;
    } else if (senderInfo.includes('@')) {
        // Drugi slučaj: samo email
        senderEmail = senderInfo.replace(/[<>]/g, '').trim(); // Ukloni < i >, te trimuj
    } else {
        // Treći slučaj: samo ime
        senderName = senderInfo.trim();
        senderNameInputField.value = senderName;
    }

    return {
        senderName: `${senderName}`,
        senderEmail: `${senderEmail}`
    };
}