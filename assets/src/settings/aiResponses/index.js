import { loadBaseView } from "./onLoadHandler";
import { regulateCreateButton } from "./createButtonHandler";
import { messageClickHandle } from "./messageClickHandler";

export function showAI(){

  loadBaseView();
  regulateCreateButton();
  messageClickHandle();


}

