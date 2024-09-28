
export function getPredefinedMessages() {
const listingInfo = document.querySelector('.listing-info')
  rcmail.http_get('plugin.aicresponsesgetrequest', {}).done( function(data) {
     // console.log("data")
     //  console.log('Predefined Messages:', data);
     // console.log(data.returnValue.length);
    document.getElementById('aic-predefined-instructions-value-textarea').value = "";
    document.getElementById('aic-predefined-instructions-title-input').value = "";
    document.getElementById('edit-id').value = "";
      const tbody = document.querySelector('#responses-table tbody');
      scrollerDisplay(listingInfo, data, tbody);
  });
}


export function getSpecificMessage(id){
rcmail.http_get('plugin.getMessageById', {id: `${id}`}).done( function(data) {
  const messageTextArea = document.getElementById('aic-predefined-instructions-value-textarea');
  const titleInput = document.getElementById('aic-predefined-instructions-title-input');
  const iframeWrapper = document.querySelector('.iframe-wrapper');
  const formDiv = document.querySelector('#form-div');



// console.log(data);
    iframeWrapper.setAttribute('hidden', 'hidden');
    formDiv.removeAttribute('hidden');
    titleInput.value = data.returnValue.title;
    messageTextArea.value = data.returnValue.value;
});
}




function scrollerDisplay(listingInfo,data,tbody){
  // console.log("uso u scrollerDidplay");
  if (data.returnValue.length>0){
    document.getElementById('responses-table').removeAttribute('hidden');
    // console.log("zadovoljio uslov scrollerDisplay");
    listingInfo.setAttribute('hidden', 'hidden');
    tbody.innerHTML = '';
    data.returnValue.forEach((message)=>{
      const tr = document.createElement('tr');
      const td = document.createElement('td');
      td.textContent = message.title;
      td.className="name";
      // console.log(message);
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


