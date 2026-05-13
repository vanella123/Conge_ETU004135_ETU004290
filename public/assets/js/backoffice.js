/* ==========================================================
   BACKOFFICE JS — backoffice.js
   Sidebar mobile toggle, shared backoffice interactions.
   ========================================================== */

(function () {
  'use strict';

  var sidebar = document.querySelector('.sidebar');
  var toggleBtn = document.querySelector('.sidebar-toggle');
  var overlay = document.querySelector('.sidebar-overlay');

  function openSidebar() {
    if (sidebar) sidebar.classList.add('is-open');
    if (overlay) overlay.classList.add('is-open');
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    if (sidebar) sidebar.classList.remove('is-open');
    if (overlay) overlay.classList.remove('is-open');
    document.body.style.overflow = '';
  }

  if (toggleBtn) {
    toggleBtn.addEventListener('click', function () {
      if (sidebar && sidebar.classList.contains('is-open')) {
        closeSidebar();
      } else {
        openSidebar();
      }
    });
  }

  if (overlay) {
    overlay.addEventListener('click', closeSidebar);
  }
})();
