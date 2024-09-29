import { createRequestSection } from "./createRequestSection.js";
import { createResultSection } from "./createResultSection.js";
import { createHelpSection } from "./createHelpSection.js";
import { createFixTextSection } from "./createFixTextSection";
import { createPredefinedInstructionsSection } from "./createPredefinedInstructionsSection";

export function createDialogContents() {
  const dialogContents = document.createElement("div");
  dialogContents.className = "xdialog-contents";

  const aicRequest = createRequestSection();
  dialogContents.appendChild(aicRequest);

  const aicResult = createResultSection();
  dialogContents.appendChild(aicResult);

  const helpDiv = createHelpSection();
  dialogContents.appendChild(helpDiv);

  const predefinedInstructionsDiv = createPredefinedInstructionsSection();
  dialogContents.appendChild(predefinedInstructionsDiv);

  const fixText = createFixTextSection();
  dialogContents.appendChild(fixText);

  return dialogContents;
}
