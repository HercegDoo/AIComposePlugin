import { capitalize } from "../../utils";

export function getRecipientInfo() {
  const inputElement = document.querySelector(
    "ul.form-control.recipient-input.ac-input.rounded-left.ui-sortable li.input input"
  );

  const ul = document.querySelector('#compose_to > div > div > ul');

  const recipients = ul.querySelectorAll('li.recipient');

const recipientsArray = [];

  recipients.forEach((recipient)=>{

    let recipientNameElement = recipient.querySelector('span.name');
    let recipientEmailElement = recipient.querySelector('span.email')

    let recipientName = recipientNameElement
      ? recipientNameElement.textContent.trim()
      : "";


    let recipientEmail = recipientEmailElement1
      ?recipientEmailElement.textContent.match(/<([^>]+)>/)?.[1]?.trim() || ""
      : "";

  const emailRecipient = {
    name : recipientName,
    email: recipientEmail
  }

  appendRecipient(emailRecipient.name, emailRecipient.email, recipientsArray);
  })

  let additionalRecipientName = "";
  let additionalRecipientEmail = "";

  // Ako recipientName sadrži '@', tretiraj ga kao email
  if (inputElement.value !== "" && inputElement.value.includes("@")) {
     additionalRecipientEmail = inputElement.value;
    const emailParts = additionalRecipientEmail.split("@")[0].split(".");
    if (emailParts.length >= 2) {
      const firstName = capitalize(emailParts[0]);
      const lastName = capitalize(emailParts[1]);
      additionalRecipientName = `${firstName} ${lastName}`;
    }
  } else {
    // Ako je recipientName samo ime, kapitalizuj ga
    additionalRecipientName = inputElement.value
      .split(" ")
      .map(word => capitalize(word))
      .join(" ");
  }

  appendRecipient(additionalRecipientName, additionalRecipientEmail, recipientsArray);

  return recipientsArray;
}

export function getRecipientData(recipientData, key = "name"){
  key = (key !== "name" && key !== "email") ? "name" : key;

  return recipientData.map(recipient => recipient[key]).join(", ");
}


function appendRecipient(name, email, array){
  (name || email) && array.push({name, email});
}

