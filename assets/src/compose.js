import {
  createButtonInToolbarMenu,
  openModal,
} from "./compose/buttonToToolbar";
import "./compose/style.css";

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
    });
});

export function getPreviousConversation() {
  return previousConversation;
}
