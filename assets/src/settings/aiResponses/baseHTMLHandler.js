export function setBaseHTML(){

  const layourMenuScrollbar = document.querySelector("#layout-list .scroller");
  const header = document.querySelector("#layout-content .header");
  const layoutContent = document.getElementById("layout-content");
  const iframeWrapper = document.querySelector(".iframe-wrapper");
  const formDiv = document.createElement("div");

  formDiv.id = "form-div";
  formDiv.setAttribute("hidden", "hidden");
  formDiv.innerHTML = `
  <form name="form" action="./" method="post" class="propform">
    <div class="formcontent">
    <input type="hidden" name="_token" id="hidden-input" class="form-control">
    <input type="hidden" name="_task" value="settings" class="form-control">
    <input type="hidden" name="_action" value="plugin.aicresponsesrequest" class="form-control">
       <div class="inner-div">
  <label for="aic-predefined-instructions-title-input"><span>Naziv</span></label> 
<input name="title" id="aic-predefined-instructions-title-input" class="form-control" type="text">
</div>
<div class="inner-div">
<label for="aic-predefined-instructions-value-textarea"> <span>Sadrzaj</span></label>
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
<a class="create" title="Kreiraj novi odgovor" data-fab="true" id="rcmbtn115" role="button" tabindex="0" aria-disabled="false" href="#" onclick="return rcmail.command(\'add\',\'\',this,event)">
<span class="inner">Kreiraj</span>
</a>
</li>
<li role="menuitem">
<a class="delete disabled" title="Obriši" id="rcmbtn116" role="button" tabindex="-1" aria-disabled="true" href="#" onclick="return rcmail.command(\'delete\',\'\',this,event)">
<span class="inner">Obriši</span>
</a>
</li>
</ul>`;

  layourMenuScrollbar.innerHTML = `
<table id="responses-table" class="listing focus" role="listbox" data-list="responses_list"  data-label-ext="Use the Create button to add a new record." data-create-command="add"><tbody></tbody></table><div class="listing-info">The list is empty. Use the Create button to add a new record.</div>
`;

}