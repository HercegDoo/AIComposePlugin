export function getRecipientInfo() {
  const aiComposeModal = document.getElementById("aic-compose-dialog");
  const recipientNameElement = document.querySelector("li.recipient span.name");
  const recipientEmailElement = document.querySelector(
    "li.recipient span.email"
  );
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
    recipientName = "";
  }

  if (recipientName && aiComposeModal) {
    recipientNameInputField.value = recipientName;
  }

  return {
    recipientName,
    recipientEmail,
  };
}
