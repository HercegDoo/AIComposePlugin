export function getRecipientInfo(){

        let recipientName = document.querySelector('li.recipient span.name');
        let recipientEmail = document.querySelector('li.recipient span.email');
        const recipientNameInputField = document.getElementById('recipient-name');

        if(recipientName && recipientName.textContent.includes('@')){
                recipientEmail = recipientName;
                recipientName = '';

        }

        const trimmedEmail = recipientEmail ? recipientEmail.textContent.replace(/[<>]/g, '').trim(): "";
        const nameText = recipientName ? recipientName.textContent : '';

        recipientNameInputField.value = nameText;

        return {
                recipientName: `${nameText}`,
                recipientEmail: `${trimmedEmail}`
        };

}