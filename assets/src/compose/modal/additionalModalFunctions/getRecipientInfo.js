export function getRecipientInfo(){

        let recipientName = document.querySelector('li.recipient span.name');
        let recipientEmail = document.querySelector('li.recipient span.email');
        const recipientNameInputField = document.getElementById('recipient-name');

        if(recipientName.textContent.includes('@')){
                recipientEmail = recipientName;
                recipientName = '';

        }

        let trimmedEmail = recipientEmail.textContent.replace(/[<>]/g, '');
        recipientNameInputField.value = recipientName.textContent;

        return {
                recipientName: `${recipientName.textContent}`,
                recipientEmail: `${trimmedEmail}`
        };

}