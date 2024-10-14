import { translation } from "../../../../utils";
import { displayModalContent } from "./displayModalContent";

export function fillPredefinedInstructionsModal() {
  let lock = rcmail.set_busy(true, 'loading');
  rcmail
    .http_get("plugin.AIComposePlugin_GetInstructionsAction", {}, lock)
    .done(function (data) {
      if (data.status === "success") {
        displayModalContent(Object.values(data.predefinedInstructions));
      } else {
        rcmail.display_message(
          `${translation("ai_predefined_error")}: ${data.message}`,
          "error"
        );
      }
    })
    .fail(function () {
      rcmail.display_message(
        `${translation("ai_predefined_instructions_error")}`,
        "error"
      );
    }).always(function(){
    rcmail.set_busy(false, "", lock);
  });
}
