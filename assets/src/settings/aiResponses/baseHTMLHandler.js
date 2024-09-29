import { translation } from "../../utils";

export function setBaseHTML(){

  const layourMenuScrollbar = document.querySelector("#layout-list .scroller");
  const header = document.querySelector("#layout-content .header");
  const layoutContent = document.getElementById("layout-content");
  const iframeWrapper = document.querySelector(".iframe-wrapper");
  const formDiv = document.createElement("div");

  formDiv.id = "form-div";
  formDiv.setAttribute("hidden", "hidden");
  formDiv.innerHTML = `
  <form name="form" action="./" method="post" class="propform" id="form-post-add-edit">
    <div class="formcontent">
    <input type="hidden" name="_token" id="hidden-input" class="form-control">
    <input type="hidden" name="_task" value="settings" class="form-control">
    <input type="hidden" name="_action" value="plugin.aicresponsesrequest" class="form-control">
    <input type="hidden" id="edit-id" name="id">
       <div class="inner-div">
  <label for="aic-predefined-instructions-title-input"><span id="predefined-instruction-title"></span></label> 
<input name="title" id="aic-predefined-instructions-title-input" class="form-control" type="text">
</div>
<div class="inner-div">
<label for="aic-predefined-instructions-value-textarea"> <span id="predefined-instruction-content"></span></label>
<textarea name="value" id="aic-predefined-instructions-value-textarea" class="form-control" cols="30" rows="10"></textarea>
</div>
<div>
<input type="submit" class="btn btn-primary" id="responses-submit">
</div>
</div>
</form>
  `;



  layoutContent.insertBefore(formDiv, iframeWrapper);

  header.innerHTML = `<ul class="menu toolbar listing iconized" id="toolbar-menu">
<li role="menuitem">
<a class="create"   role="button" tabindex="0" aria-disabled="false" href="#" >
<span class="inner" id="create-text"></span>
</a>
</li>
<li role="menuitem">
<a class="delete disabled"  id="delete-button" role="button" tabindex="-1" aria-disabled="true" href="#"">
<span class="inner" id="delete-text">Obriši</span>
</a>
</li>
</ul>`;

  document.getElementById('delete-text').textContent = translation('ai_predefined_delete');
  document.getElementById('create-text').textContent = translation('ai_predefined_create');

  layourMenuScrollbar.innerHTML = `
<table id="responses-table" class="listing focus" role="listbox" ><tbody></tbody></table><div class="listing-info" id="listing-info-text"></div>
`;

  document.getElementById('listing-info-text').innerText = translation('ai_predefined_listing_empty');
  document.getElementById('predefined-instruction-title').textContent = translation('ai_predefined_title');
  document.getElementById('predefined-instruction-content').textContent = translation('ai_predefined_content');
}