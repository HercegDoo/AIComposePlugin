import { translation } from "../../utils";


export function createFixTextSection(){
  const fixTextSection = document.createElement('div');
  fixTextSection.id= "aic-fix-text-section";
 fixTextSection.setAttribute("hidden", "hidden");

  fixTextSection.innerHTML = `
<div class="fix-text-header" > <button type="button" class="btn btn-primary" id="fix-text-back-btn">${translation("ai_help_button_back")}</button>
<h3>${translation('ai_fix_selected_text_title')}</h3>
</div>
  
  <div id="fix-text-wrapper">
  <div  class="fix-select-section" id="fix-text-selected">
  <label for="selected-text">${translation('ai_selected_text')}</label>
  <textarea class="form-control" name="selected-text" id="selected-text" cols="30" rows="10" disabled></textarea>
</div>
  <div class="fix-select-section" id="fix-text-instructions">
  <label for="selected-text">${translation('ai_fix_instructions')}</label>
  <textarea class="form-control" name="fix-instructions" id="fix-instructions" cols="30" rows="10"></textarea>
</div>
<div id="aic-fix-text-button-div">
<button id="fix-text-generate-again" class ="btn btn-primary">Generate Again</button>
</div>
  
</div>`;

  return fixTextSection;

}