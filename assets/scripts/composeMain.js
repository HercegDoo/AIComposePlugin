document.addEventListener("DOMContentLoaded", function () {
    // Kreiranje <li> elementa
    console.log(44);

    let liElement = document.createElement("li");
    liElement.setAttribute("role", "menuitem");

    // Kreiranje <a> elementa
    var aElement = document.createElement("a");
    aElement.setAttribute("href", "#responses");
    aElement.setAttribute("id", "aicp-prompt-open-button");
    aElement.classList.add("auto-generate");

    // Kreiramo span element i dodajemo tekst
    var spanElement = document.createElement("span");
    spanElement.classList.add("inner");
    spanElement.innerText = "Auto-Generate";

    // Dodajemo span u a element
    aElement.appendChild(spanElement);

    // Dodajemo a element u <li> element
    liElement.appendChild(aElement);

    // Dodajemo <li> element u toolbar-menu
    let parentEl = document.querySelector("#toolbar-menu");
    parentEl.appendChild(liElement);
});