import {appendToParent, createElement, createTextNode} from "./html";

export const createButtonInToolbarMenu = function () {
    const liElement = createElement("li", { role: "menuitem" });
    const aElement = createElement("a", { href: "#responses", id: "aicp-prompt-open-button", class: "auto-generate" }, [createTextNode("AI Compose")]);
    liElement.appendChild(aElement);

    appendToParent("#toolbar-menu", liElement);
}