export function translation(key) {
  return rcmail.gettext("AIComposePlugin." + key);
}

export function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

export function getFormattedMail(mail){
  if(containsHtmlCharacters(mail)){
    const tempDiv = document.createElement("div");
    // Sanitização para prevenir XSS
    tempDiv.textContent = mail;
    mail = tempDiv.textContent.replace(/<br\s*\/?>/gi, "")
      .replace(/\s+/g, " ").replace(/\n/g, '').replace(/\s{2,}/g, ' ')
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