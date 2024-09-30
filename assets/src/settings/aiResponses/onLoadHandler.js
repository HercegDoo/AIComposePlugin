import { getPredefinedInstructions } from "./requests/getInstructionsHandler";
import { setBaseHTML } from "./baseHTMLHandler";
import { postMethodHandler } from "./requests/postMetodHandler";

export function loadBaseView() {
  setBaseHTML();
  getPredefinedInstructions();
  postMethodHandler();
}
