import { translation } from "../../utils";

export default class HelpCommands {

  constructor() {
    this.#registerCommands();
    document.getElementById('aic-example-instructions').title = translation('ai_help_title');
  }

  #registerCommands() {
    rcube_webmail.prototype.openhelpexamples = this.#openhelpexamples;
    rcube_webmail.prototype.closehelpexamples = this.#closehelpexamples;

    rcmail.enable_command('openhelpexamples', true);
    rcmail.register_command('openhelpexamples');

    rcmail.enable_command('closehelpexamples', true);
    rcmail.register_command('closehelpexamples');
  }

  #openhelpexamples() {
    document.getElementById('aic-compose-help-modal-mask').removeAttribute('hidden');
  }

  #closehelpexamples() {
    document.getElementById('aic-compose-help-modal-mask').setAttribute('hidden', 'hidden');
  }
}
