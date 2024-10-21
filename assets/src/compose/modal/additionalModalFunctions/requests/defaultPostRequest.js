import { sendPostRequest } from "./sendPostRequest";
import { getPreviousGeneratedInsertedEmail } from "../insertEmailHandler";

export function sendDefaultPostRequest() {
  const generateEmailButton = document.getElementById("generate-email-button");
  generateEmailButton?.addEventListener("click", () => {
    let previousGeneratedEmail = getPreviousGeneratedInsertedEmail() !== "" ? getPreviousGeneratedInsertedEmail() : "";
    sendPostRequest(previousGeneratedEmail);
  });
}
