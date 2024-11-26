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
import HelpCommands from "./compose/commands/helpExamplesCommands";
import FixTextModalButtonCommands from "./compose/commands/fixTextButtonCommands";
import ToolTipAvailability from "./compose/commands/setToolTipAvailability";

document.addEventListener("DOMContentLoaded", function () {
  createButtonInToolbarMenu();
new HelpCommands();
new ToolTipAvailability();
new FixTextModalButtonCommands();

  const inputRect = document.getElementById('composebody').getBoundingClientRect();
  console.log(`inputRectBottom: ${inputRect.bottom}`);
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
