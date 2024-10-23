import { capitalize } from "../../../utils";

export function getRecipientInfo() {
  const aiComposeModal = document.getElementById("aic-compose-dialog"),
    recipientNameElement = document.querySelector("li.recipient span.name"),
    recipientEmailElement = document.querySelector("li.recipient span.email");

  const inputElement = document.querySelector(
    "ul.form-control.recipient-input.ac-input.rounded-left.ui-sortable li.input input"
  );

  const recipientNameInputField = document.getElementById("recipient-name");

  let recipientName = recipientNameElement
    ? recipientNameElement.textContent.trim()
    : inputElement.value;

  let recipientEmail = recipientEmailElement
    ? recipientEmailElement.textContent.replace(/[<>]/g, "").trim()
    : "";

  // Ako recipientName sadrži '@', tretiraj ga kao email
  if (recipientName.includes("@")) {
    recipientEmail = recipientName;

    const emailParts = recipientEmail.split("@")[0].split(".");
    if (emailParts.length >= 2) {
      const firstName = capitalize(emailParts[0]);
      const lastName = capitalize(emailParts[1]);
      recipientName = `${firstName} ${lastName}`;
    }
  } else if (recipientName) {
    // Ako je recipientName samo ime, kapitalizuj ga
    recipientName = recipientName
      .split(" ")
      .map(word => capitalize(word))
      .join(" ");
  }

  if (recipientName && aiComposeModal) {
    recipientNameInputField.value = recipientName;
  }

  return {
    recipientName,
    recipientEmail,
  };
}