export const createPredefinedInstructionsButtonInToolbarMenu = function () {
  const parentMenu = document.getElementById("toolbar-menu");
  const liElement1 = document.createElement("li");
  liElement1.id = "aic-predefined-instructions-button-li";
  liElement1.setAttribute("role", "menuitem");
  parentMenu.append(liElement1);

  liElement1.innerHTML = `
<a href="#responses" class="responses active" label="responses" title="Insert a response" unselectable="on" tabindex="2" data-popup="responses-menu" aria-haspopup="true" aria-expanded="false" aria-owns="responses-menu" data-original-title="Insert a response"><span class="inner">Responses</span></a>`;

  const popupdiv = document.createElement('div');
popupdiv.id = "popup";

  popupdiv.style.height = "200px";
 popupdiv.style.border="3px solid red";

  const h3 = document.createElement('h1');
 h3.textContent="kdsg"
  popupdiv.append(h3);
  popupdiv.classList.add('popover');
 document.body.append(popupdiv);

  const popoverButton = liElement1;
  const popover = document.getElementById('popup');

// Funkcija za pozicioniranje popovera
  function positionPopover() {
    const buttonRect = popoverButton.getBoundingClientRect();
    const popoverRect = popover.getBoundingClientRect();

    // Izračunaj poziciju za centriranje popovera
    const leftPosition = buttonRect.left + (buttonRect.width / 2) - (popoverRect.width / 2);

    // Postavi popover u odnosu na dugme
    popover.style.top = buttonRect.bottom + 'px'; // Ispod dugmeta
    popover.style.left = leftPosition + 'px'; // Centriranje
  }

// Kada se klikne na dugme
  popoverButton.addEventListener('click', function(event) {
    event.preventDefault(); // Sprečava default ponašanje linka

    // Ako je popover vidljiv, sakrij ga, inače ga prikaži
    if (popover.style.display === 'block') {
      popover.style.display = 'none';
    } else {
      popover.style.display = 'block';
      positionPopover(); // Prvi put pozicioniraj popover
    }
  });

// Zatvaranje popovera ako se klikne bilo gdje izvan njega
  document.addEventListener('click', function(event) {
    if (!popover.contains(event.target) && !popoverButton.contains(event.target)) {
      popover.style.display = 'none';
    }
  });

// Pozicioniranje popovera na promenu veličine prozora
  window.addEventListener('resize', function() {
    if (popover.style.display === 'block') {
      positionPopover(); // Ponovo pozicioniraj popover prilikom promene veličine
    }
  });








};