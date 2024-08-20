import { createComposeModalElements } from "./modalElements";
import "../../../compose/modalStyle.css";

export function createComposeModal() {
    const aicComposeModal = document.createElement("div");
    aicComposeModal.id = "aic-compose-modal";

    // Kreiraj overlay div za zamagljivanje pozadine
    const overlay = document.createElement("div");
    overlay.className = "overlay";
    document.body.prepend(overlay);
    document.body.classList.add("modal-open");

    // Pozovi funkciju koja dodaje sve elemente u modal
    createComposeModalElements(aicComposeModal);

    // Dodaj glavni div u body
    document.body.prepend(aicComposeModal);

    // Funkcija za zatvaranje modala
    const closeButton = document.getElementById("aic-close-button");
    closeButton.addEventListener("click", function () {
        aicComposeModal.remove();
        overlay.remove(); // Ukloni overlay kad se modal zatvori
        document.body.classList.remove("modal-open"); // Ukloni zamagljivanje pozadine
    });
}