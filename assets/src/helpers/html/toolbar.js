import { appendToParent, createElement, createTextNode } from "./html";
import { createComposeModal } from "./modal/index.js";

export const createButtonInToolbarMenu = function () {
    const liElement = createElement("li", { role: "menuitem" });
    const aElement = createElement(
        "a",
        {
            href: "#responses",
            id: "aicp-prompt-open-button",
            class: "auto-generate",
            role: "button",
        },
        [createTextNode("AI Compose")]
    );
    liElement.appendChild(aElement);

    appendToParent("#toolbar-menu", liElement);
    return aElement;
};

export const openModal = function () {
    createComposeModal();
};