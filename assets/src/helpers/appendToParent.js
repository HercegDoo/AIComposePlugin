const appendToParent = function (parentSelector, child) {
    const parentEl = document.querySelector(parentSelector);
    if (parentEl) {
        parentEl.appendChild(child);
    }
};

export default appendToParent;
