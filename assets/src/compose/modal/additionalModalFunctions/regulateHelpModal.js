

export function regulateHelpModal() {
    const instructionExampleBtn = document.getElementById("instruction-example");
    const request = document.getElementById("aic-result");
    const result = document.getElementById("aic-request");
    const helpContent = document.getElementById("aic-compose-help");
    const backBtn = document.getElementById("help-back-btn");
    const helpAs = document.querySelectorAll(".help-a");
    const instructionsTextarea = document.getElementById("aic-instructions");



    instructionExampleBtn?.addEventListener('click', (e)=>{
        e.stopPropagation();
        openHelpModal();
    });

    backBtn?.addEventListener('click', (e)=>{
        e.stopPropagation();
        closeHelpModal();
    });

    helpAs?.forEach((helpA)=>{
        helpA.addEventListener('click', (event)=>{
            event.stopPropagation()
              closeHelpModal();
              instructionsTextarea.value = event.target.previousElementSibling.innerText;
        });
    });

    function openHelpModal(){
        request.setAttribute("hidden", "true");
        result.setAttribute("hidden", "true");
        helpContent.removeAttribute("hidden");
    }

    function closeHelpModal(){
        request.removeAttribute("hidden");
        result.removeAttribute("hidden");
        helpContent.setAttribute("hidden", "true");
    }

}