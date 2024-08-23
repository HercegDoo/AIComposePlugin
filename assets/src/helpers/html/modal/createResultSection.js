export function createResultSection() {
    const aicResult = document.createElement("div");
    aicResult.id = "aic-result";
    aicResult.innerHTML = `<textarea id="aic-email" class="form-control"></textarea>`;

    return aicResult;
}
