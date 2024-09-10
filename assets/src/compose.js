import {
  createButtonInToolbarMenu,
  openModal,
} from "./compose/buttonToToolbar";
import "./compose/style.css";
import {getRecipientInfo} from "./compose/modal/additionalModalFunctions/getRecipientInfo";

let previousConversation = "";

document.addEventListener("DOMContentLoaded", function () {
  createButtonInToolbarMenu();

  //Uzimanje prethodnog maila ako postoji
  const previousConversationTextareaElement = document.querySelector(
    "#composebodycontainer textarea"
  );
  if (previousConversationTextareaElement.value.trim() !== "") {
    previousConversation = previousConversationTextareaElement.value.trim();
  }

  document
    .getElementById("aicp-prompt-open-button")
    .addEventListener("click", (e) => {
      e.preventDefault();
      openModal();
      getRecipientInfo();
    });
});

export function getPreviousConversation() {
  return previousConversation;
}
