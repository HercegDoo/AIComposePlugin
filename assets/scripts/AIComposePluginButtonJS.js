const AIComposePluginButtonJS = (function () {

    const _init = function () {
        console.log("Pokrenuo init");
        createButton("AI Compose");
    }

    const createButton = function (name) {
        const liElement = createElement("li", { role: "menuitem" });
        const aElement = createElement("a", { href: "#responses", id: "aicp-prompt-open-button", class: "auto-generate" }, [createTextNode(name)]);
        liElement.appendChild(aElement);

        appendToParent("#toolbar-menu", liElement);
    }

    const createElement = function (tag, attributes = {}, children = []) {
        const element = document.createElement(tag);
        Object.keys(attributes).forEach(attr => {
            element.setAttribute(attr, attributes[attr]);
        });
        children.forEach(child => element.appendChild(child));
        return element;
    }

    const createTextNode = function (text) {
        return document.createTextNode(text);
    }

    const appendToParent = function (parentSelector, child) {
        const parentEl = document.querySelector(parentSelector);
        if (parentEl) {
            parentEl.appendChild(child);
        }
    }

    return {
        init: _init
    }
})();

export  default  AIComposePluginButtonJS;