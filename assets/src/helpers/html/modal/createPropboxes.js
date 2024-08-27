export function createRecipientPropbox() {
    const recipientPropbox = document.createElement("div");
    recipientPropbox.className = "propbox";
    recipientPropbox.innerHTML = `<div>
        <label for="aic-to">
            <span class="regular-size">Recipient's name</span>
            <span class="small-size">Recipient</span>
        </label>
        <span class="fa-solid fa-circle-info xinfo right"><div>The name of the recipient of the new email. You can specify more than one name, or use strings like "everybody," "team," etc.</div></span>
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
            <span class="small-size">Sender</span>
        </label>
        <span class="fa-solid fa-circle-info xinfo"><div>The new email will be signed with this name.</div></span>
    </div>
    <input type="text" class="form-control">`;

    return senderPropbox;
}

export function createStylePropbox() {
    const stylePropbox = document.createElement("div");
    stylePropbox.className = "propbox";
    stylePropbox.innerHTML = `<div>
        <label for="aic-style">Style</label>
        <span class="fa-solid fa-circle-info xinfo"><div>The preferred writing style for the new email.</div></span>
    </div>
    <select id="aic-style" ng-model="data.style" ng-disabled="busy" class="form-control custom-select pretty-select ng-pristine ng-untouched ng-valid ng-not-empty">
        <option hidden value="assertive" >Assertive</option>
        <option hidden value="casual" >Casual</option>
        <option hidden value="enthusiastic" >Enthusiastic</option>
        <option hidden value="funny" >Funny</option>
        <option hidden value="informational" >Informational</option>
        <option hidden value="persuasive" >Persuasive</option>
    </select>`;

    return stylePropbox;
}

export function createLengthPropbox() {
    const lengthPropbox = document.createElement("div");
    lengthPropbox.className = "propbox";
    lengthPropbox.innerHTML = `<div>
        <label for="aic-length">Length</label>
        <span class="fa-solid fa-circle-info xinfo"><div>The desired length of the new email.</div></span>
    </div>
    <select id="aic-length" class="form-control custom-select pretty-select">
        <option  value="short">Short</option>
        <option  value="medium">Medium</option>
        <option  value="long">Long</option>
    </select>`;

    return lengthPropbox;
}

export function createCreativityPropbox() {
    const creativityPropbox = document.createElement("div");
    creativityPropbox.className = "propbox";
    creativityPropbox.innerHTML = `<div>
        <label for="aic-creativity">Creativity</label>
        <span class="fa-solid fa-circle-info xinfo"><div>The level of creativity you'd like in the new email.</div></span>
    </div>
    <select id="aic-creativity" class="form-control custom-select pretty-select ">
        <option  value="low">Low</option>
        <option  value="medium">Medium</option>
        <option  value="high" >High</option>
    </select>`;

    return creativityPropbox;
}

export function createLanguagePropbox() {
    const languagePropbox = document.createElement("div");
    languagePropbox.className = "propbox";
    languagePropbox.innerHTML = `<div>
        <label for="aic-language">Language</label>
        <span class="fa-solid fa-circle-info xinfo"><div>The language in which the new email will be written.</div></span>
    </div>
    <select id="aic-language" class="form-control custom-select pretty-select">
        <option  value="en" >English</option>
        <option  value="es" >Spanish</option>
        <option  value="fr" >French</option>
        <option  value="de" >German</option>
        <option  value="it" >Italian</option>
        <option  value="ja" >Japanese</option>
        <option  value="zh" >Chinese</option>
    </select>`;

    return languagePropbox;
}
