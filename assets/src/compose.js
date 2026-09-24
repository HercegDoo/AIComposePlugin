import "./compose/styles.css";

import HelpCommands from "./compose/commands/helpExamplesCommands";
import ToolTipAvailability from "./compose/commands/setToolTipAvailability";
import GenerateMail from "./compose/commands/sendPostRequest";
import FixTextCommands from "./compose/commands/fixTextCommands";
import GenerateSubject from "./compose/commands/generateSubject";
import {
  expandInstructionHeightBasedOnInput,
  handleInstructionHeight,
} from "./compose/emailHelpers/instructionHeightHandler";

document.addEventListener("DOMContentLoaded", function () {
new HelpCommands();
new ToolTipAvailability();
new GenerateMail();
new FixTextCommands();
new GenerateSubject();

handleInstructionHeight();
expandInstructionHeightBasedOnInput()
});
