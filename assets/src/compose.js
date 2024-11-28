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
import ToolTipAvailability from "./compose/commands/setToolTipAvailability";
import GenerateMail from "./compose/commands/sendPostRequest";
import FixTextCommands from "./compose/commands/fixTextCommands";

document.addEventListener("DOMContentLoaded", function () {
  createButtonInToolbarMenu();
new HelpCommands();
new ToolTipAvailability();
new GenerateMail();
new FixTextCommands();

  const inputRect = document.getElementById('composebody').getBoundingClientRect();
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
