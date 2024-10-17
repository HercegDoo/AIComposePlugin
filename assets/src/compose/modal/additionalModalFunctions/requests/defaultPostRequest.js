import { sendPostRequest } from "./sendPostRequest";

export function sendDefaultPostRequest() {
  const generateEmailButton = document.getElementById("generate-email-button");
  generateEmailButton.addEventListener("click", () => {
    let previousGeneratedEmail = document.getElementById('aic-email').value !== "" ? document.getElementById('aic-email').value : "";
    sendPostRequest(previousGeneratedEmail);
  });
}
