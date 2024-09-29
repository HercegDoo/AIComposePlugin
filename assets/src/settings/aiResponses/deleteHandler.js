import { getPredefinedInstructions } from "./getInstructionsHandler";
import { translation } from "../../utils";

export function handleDelete(id) {
  const deleteButton = document.getElementById('delete-button');
  deleteButton.classList.remove('disabled');

  deleteButton.onclick = () => {
    const buttons = {
      "Delete": {
        text: translation('ai_predefined_delete'),
        click: function () {
          $(this).dialog("close");
        },
        class: "mainaction delete btn btn-primary btn-danger",
        id: "popup-delete-button"
      },
      "Cancel": {
        text: translation('ai_predefined_cancel'),
        click: function () {
          $(this).dialog("close");
        },
        class: "cancel btn btn-secondary",
      }
    };


    rcmail.show_popup_dialog(translation('ai_predefined_popup_body'), translation('ai_predefined_popup_title'), buttons, {
      height: 30,
    });


    const popupDeleteButton = document.getElementById("popup-delete-button");
    if (popupDeleteButton) {
      popupDeleteButton.removeEventListener('click', deleteButtonClickListener);
    }


    const deleteButtonClickListener = () => {
      rcmail.http_post('plugin.aicDeleteInstruction', { id: `${id}` })
        .done(function (data) {
          if (data.status === 'success') {
            rcmail.display_message(translation('ai_predefined_successful_delete'), 'confirmation');
            getPredefinedInstructions();
          } else {
            rcmail.display_message(`${translation('ai_predefined_unsuccessful_delete')} ${data.message}`, 'error');
          }
        })
        .fail(function () {
          rcmail.display_message(translation('ai_predefined_unsuccessful_delete'), 'error');
        });
    };

    popupDeleteButton.addEventListener('click', deleteButtonClickListener);
  };
}
