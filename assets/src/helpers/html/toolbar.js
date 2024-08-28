import { appendToParent, createElement, createTextNode } from "./html";
import { createComposeModal } from "./modal";

export const createButtonInToolbarMenu = function () {
    const liElement = createElement("li", {
        role: "menuitem",
        id: "aic-button-li",
    });
    const spanElement = createElement(
        "span",
        { id: "aic-button-span", class: "inner" },
        [createTextNode("AI Compose")]
    );
    const aElement = createElement("a", {
        href: "#responses",
        id: "aicp-prompt-open-button",
        class: "auto-generate responses active",
        role: "button",
    });
    aElement.appendChild(spanElement);
    liElement.appendChild(aElement);

    appendToParent("#toolbar-menu", liElement);
    return aElement;
};

export const openModal = function () {
    createComposeModal();
};
