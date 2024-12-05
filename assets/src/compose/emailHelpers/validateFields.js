import { getSenderInfo, processSenderData } from "./senderDataHandler";
import { translation } from "../../utils";
import { getRecipientData, getRecipientInfo } from "./recipientDataHandler";

export function validateFields(){
  const errorArray = [];
  const senderName = processSenderData(getSenderInfo()).senderName;
  if(senderName === "") {
    errorArray.push({
      text: translation('ai_error_sender_name'),
      type: 'error'
    })
  }
    const recipientName = getRecipientData(getRecipientInfo(), "name");
    if(recipientName === ""){
      errorArray.push({
        text: translation('ai_error_recipient_name'),
        type: 'warning'
      })
    }

  return errorArray;
}

export function display_messages(array){
  array.forEach((errorObject)=>{
    rcmail.display_message(errorObject.text, errorObject.type);
  })
}

export function errorPresent(array) {
  for (const errorObject of array) {
    if (errorObject.type === "error") {
      return true;
    }
  }
  return false;
}