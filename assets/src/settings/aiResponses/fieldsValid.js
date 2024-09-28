export function fieldsValid(){
  const predefinedMessage = document.getElementById('aic-predefined-instructions-value-textarea').value;
  const predefinedMessageTitle = document.getElementById('aic-predefined-instructions-title-input').value ;

  return predefinedMessage !== "" && predefinedMessageTitle !== "";
}