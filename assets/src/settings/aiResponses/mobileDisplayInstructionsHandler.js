
export function mobileDisplayInstructionsHandle(id){

  const targetDiv = document.querySelector('#layout-content .footer.menu.toolbar.content-frame-navigation.hide-nav-buttons'),
    prevButton = targetDiv.querySelector('.prev'),
    nextButton = targetDiv.querySelector('.next'),
    saveButton = document.querySelector('a#create-button-clone.create.button');
  console.log(saveButton);

  if (window.innerWidth < 769) {
    targetDiv.classList.remove('hidden');

    const tbody = document.querySelector('#responses-table tbody');
    const rows = tbody.querySelectorAll("tr");
    const rowsArray = Array.from(rows).map((row, index) => {
      return {
        index: index,
        element: row
      };
    });
    console.log(rowsArray);

    const elementIndex = rowsArray.findIndex(instruction => instruction.element.id === id);
    console.log(elementIndex);

// console.log("Iz mobile: ", rcmail.env.aiPredefinedInstructions);
    prevButton.classList.toggle('disabled', elementIndex === 0);
    nextButton.classList.toggle('disabled', elementIndex === rowsArray.length - 1);

    addButtonListeners(prevButton, nextButton, saveButton ,elementIndex, rowsArray);

    if(elementIndex >= 0){
      console.log("validan prikaz");
      prevButton.style.display = "block";
      nextButton.style.display = "block";
    }
    else if(elementIndex<0){
      prevButton.style.display = "none";
      nextButton.style.display = "none";
    }

  } else {
    targetDiv.classList.add('hidden');
  }

}

function addButtonListeners(prevButton, nextButton, saveButton, elementIndex, rowsArray) {


  prevButton.addEventListener('click', () => {
    if (elementIndex > 0) {
      elementIndex--;
      rcmail.command('addinstructiontemplate', rowsArray[elementIndex].element.id);
    }
  });

  nextButton.addEventListener('click', () => {
    if (elementIndex <  rowsArray.length - 1) {
      elementIndex++;
      rcmail.command('addinstructiontemplate',rowsArray[elementIndex].element.id);
    }
  });

  saveButton.addEventListener('click', ()=>{
    rowsArray.forEach(instruction => {
      instruction.element.classList.remove('selected', 'focused');
    });
  });
}
