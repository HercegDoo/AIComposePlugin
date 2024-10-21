import { createElementWithClassName, createElementWithId, translation } from "../../utils";

const {
  languages,
  styles,
  creativities,
  lengths,
  defaultLanguage,
  defaultCreativity,
  defaultLength,
  defaultStyle,
} = rcmail.env.aiPluginOptions;

function unCapitalizeFirstLetter(string) {
  if (string.charAt(0) === string.charAt(0).toUpperCase()) {
    string = string.charAt(0).toLowerCase() + string.slice(1);
  }
  return string;
}

function createPropbox(id, label, xinfoText, options, defaultValue) {
  const propbox = document.createElement("div");
  propbox.className = "propbox";

  const select = createElementWithId('select', `aic-${id}`);
  select.className = "form-control";
  options.forEach((option) => {
    option = unCapitalizeFirstLetter(option);
    const optionElement = document.createElement("option");
    optionElement.value = option;
    if (option === defaultValue.toLowerCase()) {
      optionElement.setAttribute("selected", "selected");
    }
    optionElement.textContent = translation(`ai_${id}_${option}`);
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
  const recipientPropbox = createElementWithClassName('div', "propbox" );
  recipientPropbox.innerHTML = `<div>
      <label for="aic-to">
          <span class="regular-size">${translation("ai_label_to")}</span>
      </label>
      <span class="xinfo right"><div>${translation("ai_tip_to")}</div></span>
  </div>
  <input type="text" id="recipient-name" class="form-control" data-parsley-required="true" >`;

  return recipientPropbox;
}

export function createSenderPropbox() {
  const senderPropbox = createElementWithClassName('div', "propbox" );
  senderPropbox.innerHTML = `<div>
      <label for="aic-from">
          <span class="regular-size">${translation("ai_label_from")}</span>
      </label>
     <span class="xinfo"><div>${translation("ai_tip_from")}</div></span> 
  </div>
  <input type="text" id="sender-name" class="form-control" data-parsley-required="true" >`;

  return senderPropbox;
}

export function createStylePropbox() {
  return createPropbox(
    "style",
    translation("setting_ai_style"),
    translation("ai_tip_style"),
    styles,
    defaultStyle
  );
}

export function createLengthPropbox() {
  return createPropbox(
    "length",
    translation("setting_ai_length"),
    translation("ai_tip_length"),
    lengths,
    defaultLength
  );
}

export function createCreativityPropbox() {
  return createPropbox(
    "creativity",
    translation("ai_label_creativity"),
    translation("ai_tip_creativity"),
    creativities,
    defaultCreativity
  );
}

export function createLanguagePropbox() {
  return createPropbox(
    "language",
    translation("ai_label_language"),
    translation("ai_tip_language"),
    languages,
    defaultLanguage
  );
}
