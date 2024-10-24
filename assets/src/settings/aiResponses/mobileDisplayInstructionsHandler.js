const state = {
  elementIndex: -1
};

let eventListenersAdded = false;
let rowsArray = [];
let prevButton, nextButton, saveButton, backButton;

function nextButtonHandler() {
  if (state.elementIndex < rowsArray.length - 1) {
    state.elementIndex++;
    rcmail.command('addinstructiontemplate', rowsArray[state.elementIndex].element.id);
  }
  prevButton.classList.toggle('disabled', state.elementIndex === 0);
  nextButton.classList.toggle('disabled', state.elementIndex === rowsArray.length - 1);
}

function previousButtonHandler() {
  if (state.elementIndex > 0) {
    state.elementIndex--;
    rcmail.command('addinstructiontemplate', rowsArray[state.elementIndex].element.id);
  }
}

function  saveButtonHandler() {
  rowsArray.forEach(instruction => {
    instruction.element.classList.remove('selected', 'focused');
  });
}

function backButtonHandler() {
  saveButtonHandler();
  state.elementIndex = -1;
}

export function mobileDisplayInstructionsHandle(id) {
  const targetDiv = document.querySelector('#layout-content .footer.menu.toolbar.content-frame-navigation.hide-nav-buttons');

  prevButton = targetDiv.querySelector('.prev');
  nextButton = targetDiv.querySelector('.next');
  saveButton = document.querySelector('a#create-button-clone.create.button');
  backButton = document.querySelector('a.button.icon.back-list-button');


  if (window.innerWidth < 769) {
    targetDiv.classList.remove('hidden');

    // Uklanjanje starih event listener-a
    if(eventListenersAdded){
      prevButton.removeEventListener('click', previousButtonHandler);
      nextButton.removeEventListener('click', nextButtonHandler);

      saveButton?.removeEventListener('click', saveButtonHandler);
      backButton.removeEventListener('click', backButtonHandler);
    }


    const tbody = document.querySelector('#responses-table tbody');
    const rows = tbody.querySelectorAll("tr");
    rowsArray = Array.from(rows).map((row, index) => { // Ažuriranje globalnog rowsArray
      return {
        index: index,
        element: row
      };
    });

    state.elementIndex =  rowsArray.findIndex(instruction => instruction.element.id === id);

    prevButton.classList.toggle('disabled', state.elementIndex === 0);
    nextButton.classList.toggle('disabled', state.elementIndex === rowsArray.length - 1);

    if (state.elementIndex >= 0) {
      prevButton.style.display = "block";
      nextButton.style.display = "block";
    } else {
      prevButton.style.display = "none";
      nextButton.style.display = "none";
    }

    prevButton.addEventListener('click', previousButtonHandler);
    nextButton.addEventListener('click', nextButtonHandler);
    saveButton?.addEventListener('click', saveButtonHandler);
    backButton.addEventListener('click', backButtonHandler);
    eventListenersAdded = true;
  } else {
    targetDiv.classList.add('hidden');
  }
}
