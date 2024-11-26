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
import ButtonsAvailability from "./compose/commands/setButtonsAvailability";
import FixTextModalButtonCommands from "./compose/commands/fixTextButtonCommands";
import DummyCommands from "./compose/commands/dummyCommand";
document.addEventListener("DOMContentLoaded", function () {
  createButtonInToolbarMenu();
new HelpCommands();
new ButtonsAvailability();
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
