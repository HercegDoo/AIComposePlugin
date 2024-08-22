import "../../../compose/aic-modal-additional-style/elastic.css";
import "../../../compose/aic-modal-additional-style/larry.css";
import "../../../compose/aic-modal-additional-style/xcloud.css";
import "../../../compose/aic-modal-style/styles.css";

export function createComposeModal() {
    // Kreiranje osnovnog elementa mask dijaloga
    const dialogMask = document.createElement("div");
    dialogMask.id = "aic-compose-dialog";
    dialogMask.className = "xdialog-mask ng-scope";
    dialogMask.style.display = "block";
    dialogMask.setAttribute("ng-controller", "AicController");
    dialogMask.setAttribute("ng-init", "initialize()");

    // Kreiranje dijalog boxa
    const dialogBox = document.createElement("div");
    dialogBox.className = "xdialog-box full-screen";
    dialogBox.id = "aic-compose-dialog-box";

    // Naslov i dugme za zatvaranje
    const dialogTitle = document.createElement("div");
    dialogTitle.className = "xdialog-title";
    dialogTitle.textContent = "Compose Message with AI";

    const closeButton = document.createElement("button");
    closeButton.className = "xdialog-close btn btn-secondary";
    closeButton.textContent = "×";

    dialogTitle.appendChild(closeButton);
    dialogBox.appendChild(dialogTitle);

    // Sadržaj dijaloga
    const dialogContents = document.createElement("div");
    dialogContents.className = "xdialog-contents";

    // Sekcija za zahtjev
    const aicRequest = document.createElement("div");
    aicRequest.id = "aic-request";
    aicRequest.setAttribute("ng-show", "!helpVisible");

    const properties = document.createElement("div");
    properties.className = "properties";

    // Kreiranje polja za unos primatelja
    const recipientPropbox = document.createElement("div");
    recipientPropbox.className = "propbox";
    recipientPropbox.innerHTML = `<div>
            <label for="aic-to">
                <span class="regular-size">Recipient's name</span>
                <span class="small-size">Recipient</span>
            </label>
            <span class="xinfo right"><div>The name of the recipient of the new email. You can specify more than one name, or use strings like "everybody," "team," etc.</div></span>
        </div>
        <input type="text" class="form-control ng-pristine ng-valid ng-empty ng-touched" id="aic-to" ng-model="data.to" ng-disabled="busy">`;
    properties.appendChild(recipientPropbox);

    // Kreiranje polja za unos pošiljaoca
    const senderPropbox = document.createElement("div");
    senderPropbox.className = "propbox";
    senderPropbox.innerHTML = `<div>
            <label for="aic-from">
                <span class="regular-size">Sender's name</span>
                <span class="small-size">Sender</span>
            </label>
            <span class="xinfo"><div>The new email will be signed with this name.</div></span>
        </div>
        <input type="text" class="form-control ng-pristine ng-untouched ng-valid ng-empty" id="aic-from" ng-model="data.from" ng-disabled="busy">`;
    properties.appendChild(senderPropbox);

    // Kreiranje select polja za stil
    const stylePropbox = document.createElement("div");
    stylePropbox.className = "propbox";
    stylePropbox.innerHTML = `<div>
            <label for="aic-style">Style</label>
            <span class="xinfo"><div>The preferred writing style for the new email.</div></span>
        </div>
        <select id="aic-style" ng-model="data.style" ng-disabled="busy" class="form-control custom-select pretty-select ng-pristine ng-untouched ng-valid ng-not-empty">
            <option ng-repeat="(key, name) in styleOptions" value="assertive" class="ng-binding ng-scope">Assertive</option>
            <option ng-repeat="(key, name) in styleOptions" value="casual" class="ng-binding ng-scope">Casual</option>
            <option ng-repeat="(key, name) in styleOptions" value="enthusiastic" class="ng-binding ng-scope">Enthusiastic</option>
            <option ng-repeat="(key, name) in styleOptions" value="funny" class="ng-binding ng-scope">Funny</option>
            <option ng-repeat="(key, name) in styleOptions" value="informational" class="ng-binding ng-scope">Informational</option>
            <option ng-repeat="(key, name) in styleOptions" value="professional" class="ng-binding ng-scope" selected="selected">Professional</option>
            <option ng-repeat="(key, name) in styleOptions" value="urgent" class="ng-binding ng-scope">Urgent</option>
            <option ng-repeat="(key, name) in styleOptions" value="witty" class="ng-binding ng-scope">Witty</option>
        </select>`;
    properties.appendChild(stylePropbox);

    // Kreiranje select polja za dužinu
    const lengthPropbox = document.createElement("div");
    lengthPropbox.className = "propbox";
    lengthPropbox.innerHTML = `<div>
            <label for="aic-length">Length</label>
            <span class="xinfo"><div>The preferred length of the new email.</div></span>
        </div>
        <select id="aic-length" ng-model="data.length" ng-disabled="busy" class="form-control custom-select pretty-select ng-pristine ng-untouched ng-valid ng-not-empty">
            <option ng-repeat="(key, name) in lengthOptions" value="short" class="ng-binding ng-scope">Short</option>
            <option ng-repeat="(key, name) in lengthOptions" value="medium" class="ng-binding ng-scope" selected="selected">Medium</option>
            <option ng-repeat="(key, name) in lengthOptions" value="long" class="ng-binding ng-scope">Long</option>
        </select>`;
    properties.appendChild(lengthPropbox);

    // Kreiranje select polja za kreativnost
    const creativityPropbox = document.createElement("div");
    creativityPropbox.className = "propbox";
    creativityPropbox.innerHTML = `<div>
            <label for="aic-creativity">Creativity</label>
            <span class="xinfo"><div>Higher creativity will generate more unpredictable and diverse emails; lower creativity will generate emails that are more factual and to the point.</div></span>
        </div>
        <select id="aic-creativity" ng-model="data.creativity" ng-disabled="busy" class="form-control custom-select pretty-select ng-pristine ng-untouched ng-valid ng-not-empty">
            <option ng-repeat="(key, name) in creativityOptions" value="low" class="ng-binding ng-scope">Low</option>
            <option ng-repeat="(key, name) in creativityOptions" value="medium" class="ng-binding ng-scope" selected="selected">Medium</option>
            <option ng-repeat="(key, name) in creativityOptions" value="high" class="ng-binding ng-scope">High</option>
        </select>`;
    properties.appendChild(creativityPropbox);

    // Kreiranje select polja za jezik
    const languagePropbox = document.createElement("div");
    languagePropbox.className = "propbox";
    languagePropbox.innerHTML = `<div>
            <label for="aic-language">Language</label>
            <span class="xinfo"><div>The language in which the new email should be written. Specify "No change" to write in the language in which the instructions are given.</div></span>
        </div>
        <select id="aic-language" ng-model="data.language" ng-disabled="busy" class="form-control custom-select pretty-select ng-pristine ng-untouched ng-valid ng-not-empty">
            <option ng-repeat="(key, name) in languageOptions" value="-" class="ng-binding ng-scope" selected="selected">No change</option>
            <option ng-repeat="(key, name) in languageOptions" value="ar" class="ng-binding ng-scope">Arabic (العربية)</option>
            <option ng-repeat="(key, name) in languageOptions" value="bg_BG" class="ng-binding ng-scope">Bulgarian (Български)</option>
            <option ng-repeat="(key, name) in languageOptions" value="zh_CN" class="ng-binding ng-scope">Chinese (简体中文)</option>
            <option ng-repeat="(key, name) in languageOptions" value="zh_TW" class="ng-binding ng-scope">Chinese (正體中文)</option>
            <option ng-repeat="(key, name) in languageOptions" value="en_US" class="ng-binding ng-scope">English (US)</option>
            <option ng-repeat="(key, name) in languageOptions" value="en_GB" class="ng-binding ng-scope">English (GB)</option>
            <!-- Ovdje dolaze ostale opcije jezika -->
        </select>`;
    properties.appendChild(languagePropbox);

    // Dodavanje properties sekcije u zahtjev
    aicRequest.appendChild(properties);

    // Polje za unos instrukcija
    const instructionsDiv = document.createElement("div");
    instructionsDiv.className = "instructions";
    instructionsDiv.innerHTML = `<div>
            <label for="aic-instructions">Instructions</label>
            <span class="xinfo right"><div>Instructions on what to include in the new email. Click the Instruction Examples button to see some examples.</div></span>
        </div>
        <textarea id="aic-instructions" class="form-control ng-pristine ng-untouched ng-valid ng-empty" placeholder="Example: ask to change the appointment for next week" ng-model="data.instructions" ng-disabled="busy"></textarea>`;
    aicRequest.appendChild(instructionsDiv);

    // Sekcija za generiranje emaila
    const generateContainer = document.createElement("div");
    generateContainer.className = "generate-container";
    generateContainer.innerHTML = `<button type="button" class="btn btn-primary" ng-click="generate()" ng-show="!busy">
            <span ng-show="!generated">Generate Email</span>
            <span style="display: none;" ng-show="generated" class="ng-hide">Generate Again</span>
        </button>
        <button type="button" class="btn btn-default" ng-click="helpVisible = true" ng-disabled="busy">Instruction Examples</button>`;
    aicRequest.appendChild(generateContainer);

    // Dodavanje zahtjeva u dijalog sadržaj
    dialogContents.appendChild(aicRequest);

    // Sekcija za rezultate
    const aicResult = document.createElement("div");
    aicResult.id = "aic-result";
    aicResult.setAttribute("ng-show", "!helpVisible");
    aicResult.innerHTML = `<textarea id="aic-email" class="form-control ng-pristine ng-untouched ng-valid ng-empty" ng-model="email" ng-disabled="busy"></textarea>`;
    dialogContents.appendChild(aicResult);

    // Pomoć dijalog (sakriven)
    const helpDiv = document.createElement("div");
    helpDiv.id = "aic-compose-help";
    helpDiv.className = "ng-hide";
    helpDiv.setAttribute("ng-show", "helpVisible");
    helpDiv.style.display = "none"; // Sakriva cijeli dio Help & Examples
    helpDiv.innerHTML = `<div class="help-header">
            <button type="button" class="btn btn-primary" ng-click="helpVisible = false" style="display:none;">&lt; Back</button> <!-- Sakriva dugme Back -->
            <h3>Help & Examples</h3>
        </div>
        <div class="help-content">
            <p>Tell the AI about the email you'd like to write...</p>
            <!-- Help content and examples go here -->
        </div>`;
    dialogContents.appendChild(helpDiv);

    // Dodavanje sadržaja u glavni dijalog box
    dialogBox.appendChild(dialogContents);
    dialogMask.appendChild(dialogBox);

    closeButton.addEventListener("click", function () {
        document.body.removeChild(dialogMask);
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            document.body.removeChild(dialogMask);
        }
    });

    // Dodavanje u body
    document.body.appendChild(dialogMask);
}