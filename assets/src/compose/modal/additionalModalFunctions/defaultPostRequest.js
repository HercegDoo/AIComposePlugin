import { sendPostRequest } from "./sendPostRequest";


export function sendDefaultPostRequest() {
  const generateEmailButton = document.getElementById("generate-email-button");
  generateEmailButton.addEventListener("click", () => {
   sendPostRequest();
  });
}