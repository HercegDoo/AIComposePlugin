import { sendPostRequest } from "./sendPostRequest";

export function sendDefaultPostRequest() {
  const generateEmailButton = document.getElementById("generate-email-button");
  generateEmailButton.addEventListener("click", () => {
    let previousGeneratedEmail = document.getElementById('aic-result').value !== "" ? document.getElementById('aic-result').value : "";
    console.log(`Prethodno generisani mail: ${previousGeneratedEmail}`);
    sendPostRequest(previousGeneratedEmail);
  });
}
