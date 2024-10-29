export function translation(key) {
  return rcmail.gettext("AIComposePlugin." + key);
}

export function createElementWithId(tag, id) {
  const element = document.createElement(tag);
  element.id = id;
  return element;
}

export function createElementWithClassName(tag, className){
  const element = document.createElement(tag);
  element.className = className;
  return element;
}

export function openModal(request, result, modal) {
  request.setAttribute("hidden", "hidden");
  result.setAttribute("hidden", "hidden");
  modal.removeAttribute("hidden");
}

export function closeModal(
  request = document.getElementById("aic-result"),
  result = document.getElementById("aic-request"),
  modal = document.getElementById("aic-compose-predefined")
) {
  request.removeAttribute("hidden");
  result.removeAttribute("hidden");
  modal.setAttribute("hidden", "true");
}

export function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

export function getFormattedMail(mail){
  if(containsHtmlCharacters(mail)){
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = mail;
    mail = tempDiv.textContent.replace(/<br\s*\/?>/gi, "")
      .replace(/\s+/g, " ").replace(/\n/g, '').replace(/\s{2,}/g, '')
      .trim()
  }
  return mail;
}

export function containsHtmlCharacters(inputString) {
  const htmlCharacterRegex = /[<>&"']/;
  return htmlCharacterRegex.test(inputString);
}

export function formatText(text){
  return text.replace(/\\n/g, "\n")
    .replace(/\s+/g, " ")
    .trim();
}