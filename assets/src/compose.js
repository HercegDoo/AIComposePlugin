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

function generateSuggestedReply() {
  const suggestion = rcmail.env.aiReplySuggestion;
  if (
    rcmail.env.compose_mode !== "reply" ||
    !suggestion ||
    typeof suggestion.instruction !== "string" ||
    !suggestion.instruction.trim()
  ) {
    return;
  }

  const instruction = document.getElementById("aic-instruction");
  if (!instruction) return;
  instruction.value = suggestion.instruction;
  instruction.dispatchEvent(new Event("input", { bubbles: true }));

  const languageSelect = document.getElementById("aic_language_select");
  const language = String(suggestion.language || "").toLocaleLowerCase();
  const matchingOption = Array.from(languageSelect?.options || []).find(
    (option) => option.value.toLocaleLowerCase() === language
  );
  if (matchingOption) languageSelect.value = matchingOption.value;

  let attempts = 0;
  function start() {
    if (!rcmail.editor && attempts++ < 30) {
      window.setTimeout(start, 100);
      return;
    }
    rcmail.enable_command("generatemail", true);
    rcmail.command("generatemail", {
      passedInstruction: suggestion.instruction,
      fixText: "",
    });
  }
  start();
}

function initCompose() {
  new HelpCommands();
  new ToolTipAvailability();
  new GenerateMail();
  new FixTextCommands();
  new GenerateSubject();

  handleInstructionHeight();
  expandInstructionHeightBasedOnInput();
  generateSuggestedReply();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initCompose, { once: true });
} else {
  initCompose();
}
