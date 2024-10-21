
export function mobileDisplayInstructionsHandle(id){
  const targetDiv = document.querySelector('#layout-content .footer.menu.toolbar.content-frame-navigation.hide-nav-buttons'),
    prevButton = targetDiv.querySelector('.prev'),
    nextButton = targetDiv.querySelector('.next');

  if (window.innerWidth < 769) {
    targetDiv.classList.remove('hidden');

    const elementIndex = rcmail.env.aiPredefinedInstructions.findIndex(instruction => instruction.id === id.replace("rcmrow", ""));

    prevButton.classList.toggle('disabled', elementIndex === 0);
    nextButton.classList.toggle('disabled', elementIndex === rcmail.env.aiPredefinedInstructions.length - 1);

    addButtonListeners(prevButton, nextButton, elementIndex);

    prevButton.style.display = "block";
    nextButton.style.display = "block";
  } else {
    targetDiv.classList.add('hidden');
  }

}

function addButtonListeners(prevButton, nextButton, elementIndex) {


  prevButton.addEventListener('click', () => {
    if (elementIndex > 0) {
      elementIndex--;
      rcmail.command('addinstructiontemplate', rcmail.env.aiPredefinedInstructions[elementIndex].id);
    }
  });

  nextButton.addEventListener('click', () => {
    if (elementIndex < rcmail.env.aiPredefinedInstructions.length - 1) {
      elementIndex++;
      rcmail.command('addinstructiontemplate', rcmail.env.aiPredefinedInstructions[elementIndex].id);
    }
  });
}
