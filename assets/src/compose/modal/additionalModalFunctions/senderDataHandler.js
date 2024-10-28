import { capitalize } from "../../../utils";

export function getSenderInfo() {
  const iconLink = document.querySelector("a.iconlink.input-group-text");
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
    let nameParts = match[1].trim().split(" ");

    // Kapitalizuj svaku reč u imenu
    const capitalizedNameParts = nameParts.map(part => capitalize(part));

    // Spoj kapitalizovane delove u jedno ime
    senderName = capitalizedNameParts.join(" ");

    let emailCandidate = match[2].trim();
    senderEmail = emailCandidate.replace(/[<>]/g, "").trim();
  }
  else if (senderInfo.includes("@")) {
    // Drugi slučaj: samo email
    senderEmail = senderInfo.replace(/[<>]/g, "").trim(); // Ukloni < i >, te trimuj

    const emailParts = senderEmail.split("@")[0].split(".");
    if (emailParts.length > 1) {
      // Kapitalizuj svaku reč u imenu
      const capitalizedParts = emailParts.map(part => capitalize(part));
      // Spoj sve kapitalizovane delove u jedno ime
      senderName = capitalizedParts.join(" ");
    }
  }
  else {
      // Treći slučaj: samo ime
      senderName = senderInfo.trim();
    }


    return {
      senderName: `${senderName}`,
      senderEmail: `${senderEmail}`,
    };
  }

