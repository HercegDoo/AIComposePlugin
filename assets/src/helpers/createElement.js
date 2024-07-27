const createElement = function (tag, attributes = {}, children = []) {
    const element = document.createElement(tag);
    Object.keys(attributes).forEach((attr) => {
        element.setAttribute(attr, attributes[attr]);
    });
    children.forEach((child) => element.appendChild(child));
    return element;
};

export default createElement;
