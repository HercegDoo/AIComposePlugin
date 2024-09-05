//test komentar
const {
    languages, styles, creativities, lengths,
    defaultLanguage, defaultCreativity, defaultLength, defaultStyle
} = rcmail.env.aiPluginOptions;

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function createPropbox(id, label, xinfoText, options, defaultValue) {
    const propbox = document.createElement("div");
    propbox.className = "propbox";

    const select = document.createElement("select");
    select.id = id;
    select.className = "form-control";

    options.forEach(option => {
        const optionElement = document.createElement("option");
        optionElement.value = option;
        if(option === defaultValue){optionElement.setAttribute('selected', 'true');}
        optionElement.textContent = capitalizeFirstLetter(option);
        select.appendChild(optionElement);

    });

    propbox.innerHTML = `
        <div>
            <label for="${id}">
                <span class="regular-size">${label}</span>
            </label>
            <span class="xinfo"><div>${xinfoText}</div></span>
        </div>
    `;

    propbox.appendChild(select);

    return propbox;
}


export function createRecipientPropbox() {
    const recipientPropbox = document.createElement("div");

    recipientPropbox.className = "propbox";
    recipientPropbox.innerHTML = `<div>
      <label for="aic-to">
          <span class="regular-size">Recipient's name</span>
      </label>
      <span class="xinfo right"><div>The name of the recipient of the new email. You can specify more than one name, or use strings like "everybody," "team," etc.</div></span>
  </div>
  <input type="text" class="form-control">`;

    return recipientPropbox;
}

export function createSenderPropbox() {
    const senderPropbox = document.createElement("div");
    senderPropbox.className = "propbox";
    senderPropbox.innerHTML = `<div>
      <label for="aic-from">
          <span class="regular-size">Sender's name</span>
      </label>
     <span class="xinfo"><div>The new email will be signed with this name.</div></span> 
  </div>
  <input type="text" class="form-control">`;

    return senderPropbox;
}

export function createStylePropbox() {
    return createPropbox("aic-style", "Style", "The preferred writing style for the new email.", styles, defaultStyle);
}

export function createLengthPropbox() {
    return createPropbox("aic-length", "Length", "The desired length of the new email", lengths, defaultLength);
}


export function createCreativityPropbox() {
    return createPropbox("aic-creativity", "Creativity", "The level of creativity you'd like in the new email.", creativities, defaultCreativity);
}

export function createLanguagePropbox() {
    return createPropbox("aic-language", "Language", "The language in which the new email will be written.", languages, defaultLanguage);
}


