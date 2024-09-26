import {getPredefinedMessages} from "./getMessagesHandler";
import { setBaseHTML } from "./baseHTMLHandler";

export function loadBaseView() {
  setBaseHTML();
  getPredefinedMessages();
}
