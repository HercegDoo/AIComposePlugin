
export default class HelpCommands {

  constructor() {
    this.#registerCommands();
  }

  #registerCommands() {
    rcube_webmail.prototype.openhelpexamples = this.#openhelpexamples;
    rcube_webmail.prototype.closehelpexamples = this.#closehelpexamples;

    rcmail.enable_command('openhelpexamples', true);
    rcmail.register_command('openhelpexamples', rcube_webmail.prototype.openhelpexamples);

    rcmail.enable_command('closehelpexamples', true);
    rcmail.register_command('closehelpexamples', rcube_webmail.prototype.closehelpexamples);
  }

  #openhelpexamples() {
    document.getElementById('aic-compose-help-modal-mask').removeAttribute('hidden');
  }

  #closehelpexamples() {
    document.getElementById('aic-compose-help-modal-mask').setAttribute('hidden', 'hidden');
  }
}
