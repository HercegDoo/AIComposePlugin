
export default class Test {

  constructor() {
    this.#registerCommands();
  }

  #registerCommands() {
    rcube_webmail.prototype.openfixtextmodal = this.#openfixtextmodal;
    rcube_webmail.prototype.closefixtextmodal= this.#closefixtextmodal;

    rcmail.enable_command('openfixtextmodal', true);
    rcmail.register_command('openfixtextmodal', rcube_webmail.prototype.openfixtextmodal);

    rcmail.enable_command('closefixtextmodal', true);
    rcmail.register_command('closefixtextmodal', rcube_webmail.prototype.closefixtextmodal);
  }

  #openfixtextmodal() {
    document.getElementById('aic-fix-text-modal-mask').removeAttribute( 'hidden');
  }

  #closefixtextmodal() {
    document.getElementById('aic-fix-text-modal-mask').setAttribute('hidden', 'hidden');
  }
}
