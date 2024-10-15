import {
  createButtonInToolbarMenu,
  openModal,
} from "./compose/aiComposeButtonToToolbar";
import "./compose/style.css";
import { getRecipientInfo } from "./compose/modal/additionalModalFunctions/recipientDataHandler";
import {
  getSenderInfo,
  processSenderData,
} from "./compose/modal/additionalModalFunctions/senderDataHandler";
import { createPredefinedInstructionsButtonInToolbarMenu } from "./compose/aiPredefinedInstructionsButtonToToolbar";
document.addEventListener("DOMContentLoaded", function () {
  createButtonInToolbarMenu();
  createPredefinedInstructionsButtonInToolbarMenu();


  document
    .getElementById("aicp-prompt-open-button")
    .addEventListener("click", (e) => {
      e.preventDefault();
      openModal();
      document.getElementById("recipient-name").value =
        getRecipientInfo().recipientName;
      document.getElementById("sender-name").value =
        processSenderData(getSenderInfo()).senderName;
    });
});
