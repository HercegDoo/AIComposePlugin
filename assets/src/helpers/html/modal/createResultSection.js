export function createResultSection() {
    const aicResult = document.createElement("div");
    aicResult.id = "aic-result";
    aicResult.innerHTML = `<i class="fa-solid fa-pen-to-square fa-xl" style="color: #b92727;"></i>
  <textarea id="aic-email" class="form-control"></textarea>`;

    return aicResult;
}
