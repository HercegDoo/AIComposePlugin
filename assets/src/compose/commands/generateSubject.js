import { translation } from "../../utils";
import { getSubject, setSubject } from "../emailHelpers/subjectHandler";

export default class GenerateSubject {
  constructor() {
    const input = document.getElementById("compose-subject");
    if (!input) {
      return;
    }

    const wrapper = document.createElement("div");
    wrapper.className = "input-group aic-subject-control";
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    const append = document.createElement("span");
    append.className = "input-group-append";
    const button = document.createElement("a");
    button.id = "aic-generate-subject-button";
    button.href = "#";
    button.tabIndex = 1;
    button.className = "input-group-text icon";
    button.setAttribute("role", "button");
    const label = translation("ai_generate_subject");
    button.setAttribute("aria-label", label);
    button.title = label;
    const inner = document.createElement("span");
    inner.className = "inner";
    inner.textContent = label;
    button.appendChild(inner);
    append.appendChild(button);
    wrapper.appendChild(append);
    button.addEventListener("click", (event) => {
      event.preventDefault();
      if (button.getAttribute("aria-disabled") !== "true") {
        this.generate(button);
      }
    });
    button.addEventListener("keydown", (event) => {
      if (event.key === " ") {
        event.preventDefault();
        button.click();
      }
    });
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
    button.classList.add("disabled");
    button.setAttribute("aria-disabled", "true");

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
        button.classList.remove("disabled");
        button.removeAttribute("aria-disabled");
      });
  }
}
