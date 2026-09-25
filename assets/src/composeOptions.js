import { initComposeOptionsSaveButton } from "./compose/emailHelpers/composeOptionsPersistence.mjs";
import { postComposeOptions } from "./compose/emailHelpers/saveComposeOptions.mjs";
import { translation } from "./utils";

// Roundcube defines rcmail before loading plugin scripts. The button itself is
// added later when the compose sidebar is rendered.
initComposeOptionsSaveButton(
  document,
  (options) => postComposeOptions(rcmail, window.fetch.bind(window), options),
  () =>
    rcmail.display_message(
      translation("ai_options_save_success"),
      "confirmation"
    ),
  () => rcmail.display_message(translation("ai_options_save_error"), "warning")
);
