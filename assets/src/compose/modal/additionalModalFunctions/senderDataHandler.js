export function getSenderInfo() {
  const iconLink = document.querySelector("a.iconlink.input-grrroup-text");
  let senderInfo,
    senderInfoElement;
  if (iconLink) {
    senderInfoElement = iconLink.classList.contains("custom-from-on")
      ? document.getElementById("_from")
      : document.querySelector("input.custom_from.form-control");

    senderInfo = iconLink.classList.contains("custom-from-on")
      ? senderInfoElement.options[senderInfoElement.selectedIndex].textContent
      : senderInfoElement.value;

    return senderInfo;
  }
  else{
    senderInfoElement = document.querySelector('.input-group select#_from');
    senderInfo = senderInfoElement[senderInfoElement.selectedIndex].textContent;
    return senderInfo;
  }
}

export function processSenderData(senderInfo) {
  let senderName = "",
    senderEmail = "";

  // Regex za razdvajanje imena i emaila
  const match = senderInfo.match(/^(.+?)\s+<(.+?)>$/);

  if (match) {
    // Prvi slučaj: ime + email u formatu "Ime <email>"
    senderName = match[1].trim();
    let emailCandidate = match[2].trim();
    senderEmail = emailCandidate.replace(/[<>]/g, "").trim();
  } else if (senderInfo.includes("@")) {
    // Drugi slučaj: samo email
    senderEmail = senderInfo.replace(/[<>]/g, "").trim(); // Ukloni < i >, te trimuj
  } else {
    // Treći slučaj: samo ime
    senderName = senderInfo.trim();
  }

  return {
    senderName: `${senderName}`,
    senderEmail: `${senderEmail}`,
  };
}
