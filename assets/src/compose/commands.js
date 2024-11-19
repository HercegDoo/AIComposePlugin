
export function loadCommands() {

  rcmail.enable_command('openhelpexamples', true);
  rcmail.register_command('openhelpexamples', rcube_webmail.prototype.openhelpexamples);


  rcube_webmail.prototype.openhelpexamples = function()
  {
    document.getElementById('aic-compose-help-modal-mask').removeAttribute('hidden');

  };


  rcmail.enable_command('closehelpexamples', true);
  rcmail.register_command('closehelpexamples', rcube_webmail.prototype.closehelpexamples);


  rcube_webmail.prototype.closehelpexamples = function()
  {
    document.getElementById('aic-compose-help-modal-mask').setAttribute('hidden', 'hidden');

  };




}