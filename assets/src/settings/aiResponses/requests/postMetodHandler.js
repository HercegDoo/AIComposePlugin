import { getPredefinedInstructions } from "./getInstructionsHandler";
import { fieldsValid } from "../fieldsValid";
import { translation } from "../../../utils";

export function postMethodHandler() {
  const form = document.getElementById("form-post-add-edit");
  form.addEventListener("submit", (e) => {
    e.preventDefault();

    let value = document.getElementById(
      "aic-predefined-instructions-value-textarea"
    ).value;
    let title = document.getElementById(
      "aic-predefined-instructions-title-input"
    ).value;
    const editMessageId = document.getElementById("edit-id").value;

    if (!fieldsValid()) {
      rcmail.display_message(
        translation("ai_predefined_invalid_input"),
        "error"
      );
      return;
    }

    rcmail
      .http_post("plugin.AIComposePlugin_CreateOrEdit", {
        title: `${title}`,
        value: `${value}`,
        id: `${editMessageId}`,
      })
      .done(function (data) {
        if (data.status === "success") {
          rcmail.display_message(
            translation("ai_predefined_successful_save"),
            "confirmation"
          );
          getPredefinedInstructions(); // Osvježi listu nakon uspješnog dodavanja
        } else {
          rcmail.display_message(
            translation("ai_predefined_unsuccessful_save") +
              ": " +
              data.message,
            "error"
          );
        }
      })
      .fail(function (data) {
        rcmail.display_message(data.message, "error");
      });
  });
}
