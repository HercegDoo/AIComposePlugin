import { createTitle } from "./createTitle";
import { createEnterInfo } from "./createEnterInfo";
import { createInstructions } from "./createInstructions";
import { createButtons } from "./createButtons";
import { createGeneratedText } from "./createGeneratedText";

export function createComposeModalElements(modal) {
    createTitle(modal);
    createEnterInfo(modal);
    createInstructions(modal);
    createButtons(modal);
    createGeneratedText(modal);
}