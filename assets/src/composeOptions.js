import { initComposeOptionPersistence } from "./compose/emailHelpers/composeOptionsPersistence.mjs";
import { postComposeOptions } from "./compose/emailHelpers/saveComposeOptions.mjs";
import { translation } from "./utils";

let optionSaveWarningShown = false;

function showOptionSaveWarning() {
  if (!optionSaveWarningShown) {
    optionSaveWarningShown = true;
    rcmail.display_message(translation("ai_options_save_error"), "warning");
  }
}

function saveComposeOptions(options) {
  return postComposeOptions(rcmail, window.fetch.bind(window), options)
    .then(() => {
      optionSaveWarningShown = false;
    })
    .catch(showOptionSaveWarning);
}

// The document listener is installed as soon as this file executes. Roundcube
// sets rcmail and its request token before including plugin scripts.
initComposeOptionPersistence(document, saveComposeOptions, null, {
  style: rcmail.env.aiPluginOptions?.defaultStyle,
  length: rcmail.env.aiPluginOptions?.defaultLength,
  creativity: rcmail.env.aiPluginOptions?.defaultCreativity,
  language: rcmail.env.aiPluginOptions?.defaultLanguage,
});
