import "./compose/styles.css";

import HelpCommands from "./compose/commands/helpExamplesCommands";
import ToolTipAvailability from "./compose/commands/setToolTipAvailability";
import GenerateMail from "./compose/commands/sendPostRequest";
import FixTextCommands from "./compose/commands/fixTextCommands";
import GenerateSubject from "./compose/commands/generateSubject";
import { initComposeOptionPersistence } from "./compose/emailHelpers/composeOptionsStorage.mjs";
import {
  expandInstructionHeightBasedOnInput,
  handleInstructionHeight,
} from "./compose/emailHelpers/instructionHeightHandler";

let optionSaveQueue = Promise.resolve();
let optionSaveWarningShown = false;

function saveComposeOptions(options) {
  const data = {
    style: options.aic_style_select,
    length: options.aic_length_select,
    creativity: options.aic_creativity_select,
    language: options.aic_language_select,
  };
  const save = () =>
    new Promise((resolve, reject) => {
      rcmail
        .http_post("plugin.aicomposeplugin_SaveComposeOptionsAction", data)
        .done((result) => {
          if (result?.status === "success") resolve();
          else reject(new Error("Could not save compose options"));
        })
        .fail(reject);
    });

  optionSaveQueue = optionSaveQueue.catch(() => {}).then(save);
  optionSaveQueue.catch(() => {
    if (!optionSaveWarningShown) {
      optionSaveWarningShown = true;
      rcmail.display_message(translation("ai_options_save_error"), "warning");
    }
  });
  return optionSaveQueue;
}

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

document.addEventListener("DOMContentLoaded", function () {
  if (document.getElementById("compose-options")) {
    let storage = null;
    try {
      storage = window.localStorage;
    } catch (_) {
      // Accessing localStorage can fail in restricted browser contexts.
    }
    initComposeOptionPersistence(
      document,
      storage,
      rcmail.env.aiPluginOptions?.storageUserId,
      saveComposeOptions
    );
  }

  new HelpCommands();
  new ToolTipAvailability();
  new GenerateMail();
  new FixTextCommands();
  new GenerateSubject();

  handleInstructionHeight();
  expandInstructionHeightBasedOnInput();
  generateSuggestedReply();
});
