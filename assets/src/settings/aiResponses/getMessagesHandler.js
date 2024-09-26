
export function getPredefinedMessages() {
  const listingInfo = document.querySelector('.listing-info')
  rcmail.http_get('plugin.aicresponsesgetrequest', {}).done( function(data) {
    if (data) {
      console.log('Predefined Messages:', data.predefinedMessages);
      const tbody = document.querySelector('#responses-table tbody');
      scrollerDisplay(listingInfo, data.predefinedMessages, tbody);
    } else {
      console.error('Greška prilikom dobijanja predefinedMessages');
    }
  });
}

function scrollerDisplay(listingInfo,data,tbody){
  if (data.length>0){
    listingInfo.setAttribute('hidden', 'hidden');
    data.forEach((message, index)=>{
      const tr = document.createElement('tr');
      const td = document.createElement('td');
      td.textContent = message.title;
      td.className="name";
      tr.id= `aicpredefined${index}`;
      tr.append(td);
      tbody.append(tr);
    })
  }

}
