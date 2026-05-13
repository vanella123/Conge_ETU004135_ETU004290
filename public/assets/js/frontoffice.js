/* ==========================================================
   FRONTOFFICE JS — frontoffice.js
   Mobile menu toggle, shared frontoffice interactions.
   ========================================================== */

(function () {
  'use strict';

  var menuBtn = document.querySelector('.mobile-menu-btn');
  var nav = document.querySelector('.nav');

  if (menuBtn && nav) {
    menuBtn.addEventListener('click', function () {
      nav.classList.toggle('is-open');
    });
  }
})();
