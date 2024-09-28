import { getPredefinedMessages } from "./getMessagesHandler";
import { translation } from "../../utils";

let deleteButtonClickListener = null;

export function handleDelete(id) {
  const deleteButton = document.getElementById('delete-button');
  deleteButton.classList.remove('disabled');

  if (deleteButtonClickListener) {
    deleteButton.removeEventListener('click', deleteButtonClickListener);
  }

  deleteButtonClickListener = (e) => {
    e.preventDefault();
    deleteButton.classList.add('disabled');

    rcmail.http_post('plugin.aicdeletemessage', { id: `${id}` })
      .done(function(data) {
        if (data.status === 'success') {
          rcmail.display_message(translation('ai_predefined_successful_delete'), 'confirmation');
          getPredefinedMessages();
        } else {
          rcmail.display_message(`${translation('ai_predefined_unsuccessful_delete')} ${ data.message}`, 'error');
        }
      })
      .fail(function() {
        rcmail.display_message(translation('ai_predefined_unsuccessful_delete'), 'error');
      });
  };

  deleteButton.addEventListener('click', deleteButtonClickListener, { once: true });
}

