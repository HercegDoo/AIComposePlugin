import { createElementWithId } from "../../utils";

export function createResultSection() {
  const aicResult = createElementWithId('div', "aic-result");
  aicResult.innerHTML = `
  <textarea id="aic-email" class="form-control"></textarea>`;

  return aicResult;
}
