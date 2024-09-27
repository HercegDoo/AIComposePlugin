import {getPredefinedMessages} from "./getMessagesHandler";
import { setBaseHTML } from "./baseHTMLHandler";
import { postMethodHandler } from "./postMetodHandler";

export function loadBaseView() {
  setBaseHTML();
  getPredefinedMessages();
  postMethodHandler();
}
