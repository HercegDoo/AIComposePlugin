import {
  createButtonInToolbarMenu,
  openModal,
} from "./compose/buttonToToolbar";
import "./compose/style.css";
import { getRecipientInfo } from "./compose/modal/additionalModalFunctions/recipientDataHandler";
import {
  getSenderInfo,
  processSenderData,
} from "./compose/modal/additionalModalFunctions/senderDataHandler";
import { getPreviousConversation } from "./compose/modal/additionalModalFunctions/getPreviousConversation";
import { detectSignature } from "./compose/modal/additionalModalFunctions/signaturesHandler";

document.addEventListener("DOMContentLoaded", function () {
  createButtonInToolbarMenu();
  getPreviousConversation();


  document
    .getElementById("aicp-prompt-open-button")
    .addEventListener("click", (e) => {
      e.preventDefault();
      openModal();
      detectSignature();
      // console.log(rcmail.env.signatures['1']['html']);
      // detectSignature();
    // console.log(getPreviousConversation(detectSignature()));
      document.getElementById('recipient-name').value = getRecipientInfo().recipientName;
      document.getElementById('sender-name').value =  processSenderData(getSenderInfo()).senderName;
    });
});
