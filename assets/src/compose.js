import { createButtonInToolbarMenu, openModal } from "./compose/buttonToToolbar";
import "./compose/style.css";

document.addEventListener("DOMContentLoaded", function () {
    createButtonInToolbarMenu();

    document.getElementById("aicp-prompt-open-button").addEventListener("click", (e) => {
        e.preventDefault();
        openModal();
    });
});