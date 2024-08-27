export function initializeCustomSelect() {
    const selectElements = [
        "aic-style",
        "aic-length",
        "aic-creativity",
        "aic-language",
    ].map((id) => document.getElementById(id));

    selectElements.forEach((selectElement) => {
        if (!selectElement) {
            console.error("Select element not found");
            return;
        }

        // Sprijeci otvaranje defaultnog dropdown-a
        selectElement.addEventListener("mousedown", function (event) {
            event.preventDefault();
            event.stopPropagation();
        });

        selectElement.addEventListener("click", function (event) {
            event.stopPropagation(); // Spreči propagaciju događaja

            // Zatvori prethodno otvoren popover
            const existingPopover = document.getElementById("custom-popover");
            if (existingPopover) {
                existingPopover.remove();
                return; // Ako postoji otvoren popover, zatvori ga i izađi iz funkcije
            }

            // Kreiraj novi popover
            const popover = document.createElement("div");
            popover.id = "custom-popover";
            popover.className = "popover select-menu fade bs-popover-bottom show";
            popover.style.position = "absolute"; // Koristi absolute za pozicioniranje
            popover.style.zIndex = "9999"; // Veći z-index od select elementa

            // Postavi širinu popover-a prema širini select elementa
            popover.style.minWidth = `${selectElement.offsetWidth}px`;
            popover.style.maxWidth = `${selectElement.offsetWidth}px`;

            // Postavi poziciju popover-a blizu select elementa
            const rect = selectElement.getBoundingClientRect();
            popover.style.top = `${
                rect.bottom -
                selectElement.parentElement.getBoundingClientRect().top +
                3
            }px`;
            popover.style.left = `${
                rect.left - selectElement.parentElement.getBoundingClientRect().left
            }px`;

            const popoverHeader = document.createElement("div");
            popoverHeader.className = "popover-header";
            const closeButton = document.createElement("a");
            closeButton.className = "button icon cancel";
            closeButton.textContent = "Close";

            closeButton.addEventListener("click", function () {
                popover.remove();
            });

            popoverHeader.appendChild(closeButton);
            popover.appendChild(popoverHeader);

            const popoverBody = document.createElement("div");
            popoverBody.className = "popover-body custom-pop";
            popoverBody.style.maxHeight = "379px";

            const ul = document.createElement("ul");
            ul.className = "listing selectable iconized popover-ul";
            ul.setAttribute("data-ident", `selectxai-${selectElement.id}`);

            Array.from(selectElement.options).forEach((option) => {
                if (option.style.display !== "none") {
                    const li = document.createElement("li");
                    const a = document.createElement("a");
                    a.href = "#";
                    a.textContent = option.text;

                    a.addEventListener("click", function (e) {
                        e.preventDefault();
                        selectElement.value = option.value; // Ažuriraj vrednost select-a
                        popover.remove();
                        selectElement.classList.add("popover-highlight"); // Dodaj highlight klasu
                        setTimeout(() => {
                            selectElement.classList.remove("popover-highlight"); // Ukloni nakon nekog vremena
                        }, 2000); // Uklanja highlight nakon 2 sekunde
                    });

                    li.appendChild(a);
                    ul.appendChild(li);
                }
            });

            popoverBody.appendChild(ul);
            popover.appendChild(popoverBody);

            selectElement.parentElement.appendChild(popover); // Dodaj popover u roditeljsku div
        });
    });

    // Zatvori popover klikom izvan njega
    document.addEventListener("click", function (e) {
        const existingPopover = document.getElementById("custom-popover");
        if (existingPopover && !existingPopover.contains(e.target)) {
            existingPopover.remove();
        }
    });

    // Zatvori popover prilikom promene veličine prozora
    window.addEventListener("resize", function () {
        const existingPopover = document.getElementById("custom-popover");
        if (existingPopover) {
            existingPopover.remove();
        }
    });
}