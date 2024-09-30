import { translation } from "../../../../utils";
import { displayModalContent } from "./displayModalContent";

export function fillPredefinedInstructionsModal() {
  rcmail
    .http_get("plugin.aicGetAllInstructions", {})
    .done(function (data) {
      if (data.status === "success") {
        displayModalContent(data.returnValue);
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
    });
}
