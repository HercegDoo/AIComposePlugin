import {
  createButtonInToolbarMenu,
  openModal,
} from "./compose/buttonToToolbar";
import "./compose/style.css";
import {getRecipientInfo} from "./compose/modal/additionalModalFunctions/getRecipientInfo";
import {getSenderInfo, processSenderData} from "./compose/modal/additionalModalFunctions/senderDataHandler";
import {getPreviousConversation} from "./compose/modal/additionalModalFunctions/getPreviousConversation";


document.addEventListener("DOMContentLoaded", function () {
  createButtonInToolbarMenu();

getPreviousConversation();

  document
    .getElementById("aicp-prompt-open-button")
    .addEventListener("click", (e) => {
      e.preventDefault();
      openModal();
      getRecipientInfo();
      processSenderData(getSenderInfo());
    });
});

