

export const createElement = function (tag, attributes = {}, children = []) {
    const element = document.createElement(tag);
    Object.keys(attributes).forEach(attr => {
        element.setAttribute(attr, attributes[attr]);
    });
    children.forEach(child => element.appendChild(child));
    return element;
}

export const createTextNode = function (text) {
    return document.createTextNode(text);
}

export const appendToParent = function (parentSelector, child) {
    const parentEl = document.querySelector(parentSelector);
    if (parentEl) {
        parentEl.appendChild(child);
    }
}