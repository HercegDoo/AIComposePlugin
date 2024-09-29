import {getPredefinedInstructions} from "./getInstructionsHandler";
import { setBaseHTML } from "./baseHTMLHandler";
import { postMethodHandler } from "./postMetodHandler";

export function loadBaseView() {
  setBaseHTML();
  getPredefinedInstructions();
  postMethodHandler();
}
