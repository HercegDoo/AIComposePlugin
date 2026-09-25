import { translation } from "../../utils";
import { getSubject, setSubject } from "../emailHelpers/subjectHandler";

export default class GenerateSubject {
  constructor() {
    const input = document.getElementById("compose-subject");
    if (!input) {
      return;
    }

    const wrapper = document.createElement("div");
    wrapper.className = "aic-subject-control";
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    const button = document.createElement("button");
    button.id = "aic-generate-subject-button";
    button.type = "button";
    button.className = "btn btn-secondary";
    button.textContent = translation("ai_generate_subject");
    button.title = translation("ai_generate_subject");
    wrapper.appendChild(button);
    button.addEventListener("click", () => this.generate(button));
  }

  generate(button) {
    const editor = rcmail.editor?.is_html() ? rcmail.editor.editor : null;
    const body = editor
      ? editor.getContent({ format: "text" })
      : document.getElementById("composebody")?.value || "";
    const instructions =
      document.getElementById("aic-instruction")?.value || "";
    if (!body.trim() && !instructions.trim()) {
      rcmail.display_message(
        translation("ai_subject_requires_content"),
        "warning"
      );
      return;
    }

    const previousSubject = getSubject();
    const selectedLanguage =
      document.getElementById("aic_language_select")?.value ||
      rcmail.env.aiPluginOptions.defaultLanguage;
    const language =
      rcmail.env.aiPluginOptions.languages.find(
        (option) => option.toLowerCase() === selectedLanguage.toLowerCase()
      ) || selectedLanguage;
    button.disabled = true;

    rcmail
      .http_post(
        "plugin.aicomposeplugin_GenerateSubjectAction",
        { body, instructions, language, subject: previousSubject },
        true
      )
      .done((data) => {
        if (data?.status === "success" && data.subject) {
          if (getSubject() === previousSubject) {
            setSubject(data.subject);
          }
        } else {
          rcmail.display_message(
            data?.message || translation("ai_request_error"),
            "error"
          );
        }
      })
      .fail(() =>
        rcmail.display_message(translation("ai_request_error"), "error")
      )
      .always(() => {
        button.disabled = false;
      });
  }
}
