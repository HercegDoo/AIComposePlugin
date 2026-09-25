import "./compose/styles.css";

import HelpCommands from "./compose/commands/helpExamplesCommands";
import ToolTipAvailability from "./compose/commands/setToolTipAvailability";
import GenerateMail from "./compose/commands/sendPostRequest";
import FixTextCommands from "./compose/commands/fixTextCommands";
import GenerateSubject from "./compose/commands/generateSubject";
import {
  composeOptionsPostData,
  initComposeOptionPersistence,
} from "./compose/emailHelpers/composeOptionsPersistence.mjs";
import { translation } from "./utils";
import {
  expandInstructionHeightBasedOnInput,
  handleInstructionHeight,
} from "./compose/emailHelpers/instructionHeightHandler";

let optionSaveWarningShown = false;

function showOptionSaveWarning() {
  if (!optionSaveWarningShown) {
    optionSaveWarningShown = true;
    rcmail.display_message(translation("ai_options_save_error"), "warning");
  }
}

function loadComposeOptions() {
  return new Promise((resolve, reject) => {
    const request = rcmail.http_get(
      "plugin.aicomposeplugin_GetComposeOptionsAction",
      {}
    );
    if (!request) {
      reject(new Error("Could not load compose options"));
      return;
    }

    request
      .done((result) => {
        if (result?.status === "success" && result.options) {
          resolve(result.options);
        } else {
          reject(new Error("Could not load compose options"));
        }
      })
      .fail(reject);
  }).catch((error) => {
    rcmail.display_message(translation("ai_options_load_error"), "warning");
    throw error;
  });
}

function saveComposeOptions(options) {
  const data = composeOptionsPostData(options);
  return new Promise((resolve, reject) => {
    rcmail
      .http_post("plugin.aicomposeplugin_SaveComposeOptionsAction", data)
      .done((result) => {
        if (result?.status === "success") {
          optionSaveWarningShown = false;
          resolve();
        } else {
          reject(new Error("Could not save compose options"));
        }
      })
      .fail(reject);
  }).catch(() => {
    showOptionSaveWarning();
  });
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

function initCompose() {
  initComposeOptionPersistence(
    document,
    saveComposeOptions,
    loadComposeOptions
  );

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
