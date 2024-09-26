export function getSpecificPredefinedMessage(id){
  const messageTextArea = document.getElementById('aic-predefined-instructions-value-textarea');
  const titleInput = document.getElementById('aic-predefined-instructions-title-input');
  const iframeWrapper = document.querySelector('.iframe-wrapper');
  const formDiv = document.querySelector('#form-div');
  rcmail
    .http_get(
      'plugin.aicr',
      {
        id: `${id}`
      },
    )
    .done(function (data) {
      iframeWrapper.setAttribute('hidden', 'hidden');
      formDiv.removeAttribute('hidden');
      titleInput.value = data['predefinedMessage'].title;
      messageTextArea.value = data['predefinedMessage'].value;

    })
    .always(function (data) {
     console.log("tu");
    });
}