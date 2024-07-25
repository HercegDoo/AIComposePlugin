
document.addEventListener("DOMContentLoaded", async function () {
    try {
        const AIComposePluginButtonJS = (await import("./AIComposePluginButtonJS.js")).default;

        AIComposePluginButtonJS.init();
    } catch (error) {
        console.error("Greška pri učitavanju modula:", error);
    }
});