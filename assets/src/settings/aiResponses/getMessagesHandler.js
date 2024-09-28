import { clearInputFields, hideDeleteButton, showInputFields} from "./displayHandler";

export function getPredefinedMessages() {
  const listingInfo = document.querySelector('.listing-info');
  const tbody = document.querySelector('#responses-table tbody');

  rcmail.http_get('plugin.aicresponsesgetrequest', {})
    .done(function(data) {
      if (data.status === 'success') {
        clearInputFields();
        document.getElementById('edit-id').value = "";
        hideDeleteButton();
        scrollerDisplay(listingInfo, data, tbody);
      } else {
        rcmail.display_message(`Greška: ${data.message}`, 'error');
      }
    })
    .fail(function() {
      rcmail.display_message('Greška prilikom dohvaćanja poruka', 'error');
    });
}



export function getSpecificMessage(id) {

  rcmail.http_get('plugin.getMessageById', { id: `${id}` })
    .done(function(data) {
      if (data.status === 'success') {
        showInputFields(data.returnValue.title, data.returnValue.value);
      } else {
        rcmail.display_message(`Greška: ${data.message}`, 'error');
      }
    })
    .fail(function() {
      rcmail.display_message('Greška prilikom dohvaćanja poruke.', 'error');
    });
}





function scrollerDisplay(listingInfo,data,tbody){
  if (data.returnValue.length>0){
    document.getElementById('responses-table').removeAttribute('hidden');
    listingInfo.setAttribute('hidden', 'hidden');
    tbody.innerHTML = '';
    data.returnValue.forEach((message)=>{
      const tr = document.createElement('tr');
      const td = document.createElement('td');
      td.textContent = message.title;
      td.className="name";
      td.id= `${message.id}`;
      tr.append(td);
      tbody.append(tr);
    })
  }
  else{
    listingInfo.removeAttribute('hidden');
    document.getElementById('responses-table').setAttribute('hidden', 'hidden');
  }


}


