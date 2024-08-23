export function createRecipientPropbox() {
    const recipientPropbox = document.createElement("div");
    recipientPropbox.className = "propbox";
    recipientPropbox.innerHTML = `<div>
      <label for="aic-to">
          <span class="regular-size">Recipient's name</span>
          <span class="small-size">Recipient</span>
      </label>
      <span class="xinfo right"><div>The name of the recipient of the new email. You can specify more than one name, or use strings like "everybody," "team," etc.</div></span>
  </div>
  <input type="text" class="form-control" id="aic-to">`;

    return recipientPropbox;
}

export function createSenderPropbox() {
    const senderPropbox = document.createElement("div");
    senderPropbox.className = "propbox";
    senderPropbox.innerHTML = `<div>
      <label for="aic-from">
          <span class="regular-size">Sender's name</span>
          <span class="small-size">Sender</span>
      </label>
      <span class="xinfo"><div>The new email will be signed with this name.</div></span>
  </div>
  <input type="text" class="form-control" id="aic-from">`;

    return senderPropbox;
}

export function createStylePropbox() {
    const stylePropbox = document.createElement("div");
    stylePropbox.className = "propbox";
    stylePropbox.innerHTML = `<div>
      <label for="aic-style">Style</label>
      <span class="xinfo"><div>The preferred writing style for the new email.</div></span>
  </div>
  <select id="aic-style"  class="form-control custom-select pretty-select">
      <option value="assertive" >Assertive</option>
      <option value="casual" >Casual</option>
      <option value="enthusiastic" >Enthusiastic</option>
      <option value="funny" >Funny</option>
      <option value="informational" >Informational</option>
      <option value="persuasive" >Persuasive</option>
  </select>`;

    return stylePropbox;
}

export function createLengthPropbox() {
    const lengthPropbox = document.createElement("div");
    lengthPropbox.className = "propbox";
    lengthPropbox.innerHTML = `<div>
      <label for="aic-length">Length</label>
      <span class="xinfo"><div>The desired length of the new email.</div></span>
  </div>
  <select id="aic-length" class="form-control custom-select pretty-select">
      <option value="short" >Short</option>
      <option value="medium" >Medium</option>
      <option value="long" >Long</option>
  </select>`;

    return lengthPropbox;
}

export function createCreativityPropbox() {
    const creativityPropbox = document.createElement("div");
    creativityPropbox.className = "propbox";
    creativityPropbox.innerHTML = `<div>
      <label for="aic-creativity">Creativity</label>
      <span class="xinfo"><div>The level of creativity you'd like in the new email.</div></span>
  </div>
  <select id="aic-creativity" class="form-control custom-select pretty-select ">
      <option value="low" class="ng-binding ng-scope">Low</option>
      <option value="medium" class="ng-binding ng-scope">Medium</option>
      <option value="high" class="ng-binding ng-scope">High</option>
  </select>`;

    return creativityPropbox;
}

export function createLanguagePropbox() {
    const languagePropbox = document.createElement("div");
    languagePropbox.className = "propbox";
    languagePropbox.innerHTML = `<div>
      <label for="aic-language">Language</label>
      <span class="xinfo"><div>The language in which the new email will be written.</div></span>
  </div>
  <select id="aic-language" class="form-control custom-select pretty-select ">
      <option  value="en" class="ng-binding ng-scope">English</option>
      <option  value="es" class="ng-binding ng-scope">Spanish</option>
      <option  value="fr" class="ng-binding ng-scope">French</option>
      <option  value="de" class="ng-binding ng-scope">German</option>
      <option  value="it" class="ng-binding ng-scope">Italian</option>
      <option  value="ja" class="ng-binding ng-scope">Japanese</option>
      <option  value="zh" class="ng-binding ng-scope">Chinese</option>
  </select>`;

    return languagePropbox;
}
