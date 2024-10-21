import { createElementWithId, translation } from "../../utils";

export function createHelpSection() {
  const helpDiv = createElementWithId('div', "aic-compose-help" );
  helpDiv.setAttribute("hidden", "true");

  helpDiv.innerHTML = `<div class="help-header">
      <button type="button" class="btn btn-primary" id="help-back-btn">${translation("ai_help_button_back")}</button>
      <h3>${translation("ai_help_title")}</h3>
  </div>
<div class="help-content">
  <p>${translation("ai_help_info")}</p>
   <ul>
    <li id="xai-help-example-1">
    <span>${translation("ai_help_example_1")}</span> <a href="javascript:void(0)" class="help-a">${translation("ai_help_use_example")}</a>
    </li>
    <li id="xai-help-example-2">
    <span>${translation("ai_help_example_2")}</span> 
    <a href="javascript:void(0)" class="help-a">${translation("ai_help_use_example")}</a>
    </li>
    <li id="xai-help-example-3">
    <span>${translation("ai_help_example_3")}</span>
     <a href="javascript:void(0)" class="help-a">${translation("ai_help_use_example")}</a>
     </li>
    <li id="xai-help-example-4"><span>${translation("ai_help_example_4")}</span>
     <a href="javascript:void(0)" class="help-a">${translation("ai_help_use_example")}</a>
     </li>
     <li id="xai-help-example-5">
     <span>${translation("ai_help_example_5")}</span> 
     <a href="javascript:void(0)" class="help-a">${translation("ai_help_use_example")}</a>
     </li>
     <li id="xai-help-example-6">
     <span>${translation("ai_help_example_6")}</span> 
     <a href="javascript:void(0)" class="help-a">${translation("ai_help_use_example")}</a>
     </li>
     <li id="xai-help-example-7">
     <span>${translation("ai_help_example_7")}</span> 
     <a href="javascript:void(0)" class="help-a">${translation("ai_help_use_example")}</a>
     </li>
     <li id="xai-help-example-8">
     <span>${translation("ai_help_example_8")}</span>
      <a href="javascript:void(0)" class="help-a">${translation("ai_help_use_example")}</a>
      </li><li id="xai-help-example-9">
      <span>${translation("ai_help_example_9")}</span>
       <a href="javascript:void(0)" class="help-a">${translation("ai_help_use_example")}</a>
       </li>
       <li id="xai-help-example-10">
       <span>${translation("ai_help_example_10")}</span> 
       <a href="javascript:void(0)" class="help-a" >${translation("ai_help_use_example")}</a>
       </li>
       <li id="xai-help-example-11">
       <span>${translation("ai_help_example_11")}</span> 
       <a href="javascript:void(0)"  class="help-a">${translation("ai_help_use_example")}</a>
       </li>
       <li id="xai-help-example-12">
       <span>${translation("ai_help_example_12")}</span> 
       <a href="javascript:void(0)" class="help-a" >${translation("ai_help_use_example")}</a>
       </li>
       <li id="xai-help-example-13">
       <span>${translation("ai_help_example_13")}</span> 
       <a href="javascript:void(0)"  class="help-a">${translation("ai_help_use_example")}</a>
       </li>
       <li id="xai-help-example-14">
       <span>${translation("ai_help_example_14")}</span>
        <a href="javascript:void(0)" class="help-a" >${translation("ai_help_use_example")}</a>
        </li>
        <li id="xai-help-example-15">
        <span>${translation("ai_help_example_15")}</span> 
        <a href="javascript:void(0)"  class="help-a">${translation("ai_help_use_example")}</a>
        </li>
        <li id="xai-help-example-16">
        <span>${translation("ai_help_example_16")}</span> 
        <a href="javascript:void(0)" class="help-a" >${translation("ai_help_use_example")}</a>
       </li>
        <li id="xai-help-example-17">
        <span>${translation("ai_help_example_17")}</span> 
        <a href="javascript:void(0)" class="help-a" >${translation("ai_help_use_example")}</a>
        </li>
        <li id="xai-help-example-18">
        <span>${translation("ai_help_example_18")}</span>
         <a href="javascript:void(0)"  class="help-a">${translation("ai_help_use_example")}</a>
         </li>
         </ul>
 </div>`;

  return helpDiv;
}
