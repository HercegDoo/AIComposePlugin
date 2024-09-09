import {translation} from "../../utils";

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
  if(string.charAt(0) === string.charAt(0).toUpperCase()){
  string = string.charAt(0).toLowerCase() + string.slice(1) }
  return string;
}



function createPropbox(id, label, xinfoText, options, defaultValue, name) {
  const propbox = document.createElement("div");
  propbox.className = "propbox";

  const select = document.createElement("select");
  select.id = id;
  select.className = "form-control";
  options.forEach((option) => {
    option = unCapitalizeFirstLetter(option);
    const optionElement = document.createElement("option");
    optionElement.value = option;
    if (option === defaultValue) {
      optionElement.setAttribute("selected", "selected");
    }
    optionElement.textContent = translation(`ai_${name}_${option}`);
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
          <span class="regular-size">${translation('ai_label_to')}</span>
      </label>
      <span class="xinfo right"><div>${translation('ai_tip_to')}</div></span>
  </div>
  <input type="text" class="form-control">`;

  return recipientPropbox;
}

export function createSenderPropbox() {
  const senderPropbox = document.createElement("div");
  senderPropbox.className = "propbox";
  senderPropbox.innerHTML = `<div>
      <label for="aic-from">
          <span class="regular-size">${translation('ai_label_from')}</span>
      </label>
     <span class="xinfo"><div>${translation('ai_tip_from')}</div></span> 
  </div>
  <input type="text" class="form-control">`;

  return senderPropbox;
}

export function createStylePropbox() {
  return createPropbox(
    "aic-style",
    translation('setting_ai_style'),
    translation('ai_tip_style'),
    styles,
    defaultStyle,
      "style"
  );
}

export function createLengthPropbox() {
  return createPropbox(
    "aic-length",
    translation('setting_ai_length'),
    translation('ai_tip_length'),
    lengths,
    defaultLength,
      'length'
  );
}

export function createCreativityPropbox() {
  return createPropbox(
    "aic-creativity",
    translation('ai_label_creativity'),
    translation('ai_tip_creativity'),
    creativities,
    defaultCreativity,
      "creativity"
  );
}

export function createLanguagePropbox() {
  return createPropbox(
    "aic-language",
    translation('ai_label_language'),
    translation('ai_tip_language'),
    languages,
    defaultLanguage,
      "language"
  );
}
