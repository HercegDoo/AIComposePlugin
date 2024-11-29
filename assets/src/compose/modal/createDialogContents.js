
import { createFixTextSection } from "./createFixTextSection";
import { createElementWithClassName } from "../../utils";

export function createDialogContents() {
  const dialogContents = createElementWithClassName('div', "xdialog-contents" );




  // const helpDiv = createHelpSection();
  // dialogContents.appendChild(helpDiv);

  const predefinedInstructionsDiv = createPredefinedInstructionsSection();
  dialogContents.appendChild(predefinedInstructionsDiv);

  const fixText = createFixTextSection();
  dialogContents.appendChild(fixText);

  return dialogContents;
}
