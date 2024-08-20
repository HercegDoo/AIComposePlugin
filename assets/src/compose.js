import { createButtonInToolbarMenu, openModal } from "./helpers/html/toolbar";
import "./compose/style.css";

document.addEventListener("DOMContentLoaded", function () {
    const OpenAIComposehhButton = createButtonInToolbarMenu();

    OpenAIComposehhButton.addEventListener("click", (e) => {
        e.preventDefault();
        openModal();
    });
});