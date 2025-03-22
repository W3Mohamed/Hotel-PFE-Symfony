document.addEventListener( 'DOMContentLoaded', function() {

    const navBtns = document.querySelectorAll('.navBtn');
    const navbars = document.querySelectorAll('.navbar');
  
    navBtns.forEach((navBtn, index) => {
      navBtn.addEventListener('click', function () {
        navbars[index].classList.toggle('hidden'); // Affiche ou cache le menu correspondant
      });
    });

});