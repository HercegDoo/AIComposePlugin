import { sendPostRequest } from "./sendPostRequest";


export function sendRequestData() {
  const generateEmailButton = document.getElementById("generate-email-button");
  generateEmailButton.addEventListener("click", () => {
   sendPostRequest();
  });
}