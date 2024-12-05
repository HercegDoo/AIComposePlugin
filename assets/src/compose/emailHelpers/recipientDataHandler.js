import { capitalize } from "../../utils";

export function getRecipientInfo() {
    const recipientNameElement = document.querySelector("li.recipient span.name"),
    recipientEmailElement = document.querySelector("li.recipient span.email");

  const inputElement = document.querySelector(
    "ul.form-control.recipient-input.ac-input.rounded-left.ui-sortable li.input input"
  );

  const ul = document.querySelector('#compose_to > div > div > ul');

  const recipients = ul.querySelectorAll('li.recipient');

const recipientsArray = [];

  recipients.forEach((recipient)=>{

    let recipientNameElement1 = recipient.querySelector('span.name');
    let recipientEmailElement1 = recipient.querySelector('span.email')

    let recipientName = recipientNameElement1
      ? recipientNameElement1.textContent.trim()
      : "";


    let recipientEmail = recipientEmailElement1
      ?recipientEmailElement1.textContent.match(/<([^>]+)>/)?.[1]?.trim() || ""
      : "";

  const emailRecipient = {
    name : recipientName,
    email: recipientEmail
  }

  if(emailRecipient.name !== "" || emailRecipient.email !== ""){
    recipientsArray.push(emailRecipient);
  }
  })

  if(inputElement.value !== ""){
    recipientsArray.push(inputElement.value);
  }




  // let recipientName = recipientNameElement
  //   ? recipientNameElement.textContent.trim()
  //   : inputElement.value;
  //
  // let recipientEmail = recipientEmailElement
  //   ? recipientEmailElement.textContent.match(/<([^>]+)>/)?.[1]?.trim() || ""
  //   : "";

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

  recipientsArray.push({
    name : additionalRecipientName,
    email : additionalRecipientEmail
  });

  return recipientsArray;
}

export function getRecipientData(recipientData, key = "name"){
  key = (key !== "name" && key !== "email") ? "name" : key;


  return recipientData.map(recipient => recipient[key]).join(", ");


}


