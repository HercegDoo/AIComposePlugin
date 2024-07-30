import createElement from './createElement.js';
import createTextNode from './createTextNode.js';
import appendToParent from './appendToParent.js';

const createButton = function (name) {
    const liElement = createElement("li", { role: "menuitem" });
    const aElement = createElement(
        "a",
        {
            href: "#responses",
            id: "aicp-prompt-open-button",
            class: "auto-generate",
            title: "Compose Message with AI",
        },
        [createTextNode(name)]
    );
    liElement.appendChild(aElement);
    appendToParent("#toolbar-menu", liElement);
};

export default createButton;
