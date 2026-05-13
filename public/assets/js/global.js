/* ==========================================================
   GLOBAL JS — global.js
   Shared utilities: AJAX forms, confirm modal, flash dismiss.
   Loaded by both frontoffice and backoffice layouts.
   ========================================================== */

(function () {
  'use strict';

  /* ── Field error helpers ── */
  function resetFieldErrors(form) {
    form.querySelectorAll('.field-error').forEach(function (node) {
      node.textContent = '';
    });
    form.querySelectorAll('.is-invalid').forEach(function (node) {
      node.classList.remove('is-invalid');
    });
  }

  function showFieldErrors(form, errors) {
    Object.entries(errors || {}).forEach(function (entry) {
      var fieldName = entry[0];
      var errorMessage = entry[1];
      var errorNode = form.querySelector('[data-field-error="' + fieldName + '"]');
      var inputNode = form.querySelector('[name="' + fieldName + '"]');
      if (errorNode) errorNode.textContent = errorMessage;
      if (inputNode) inputNode.classList.add('is-invalid');
    });
  }

  /* ── Form feedback ── */
  function setFeedback(form, type, message, errors) {
    var feedback = form.querySelector('[data-form-feedback]');
    if (!feedback) return;

    feedback.className = 'form-feedback is-visible ' + type;

    var errorEntries = Object.entries(errors || {});
    if (type === 'error' && errorEntries.length) {
      var unique = [];
      var seen = {};
      errorEntries.forEach(function (e) {
        var val = String(e[1]);
        if (!seen[val] && val !== String(message || '')) {
          unique.push(val);
          seen[val] = true;
        }
      });
      var list = unique.map(function (v) { return '<li>' + v + '</li>'; }).join('');
      feedback.innerHTML = list
        ? '<strong>' + message + '</strong><ul style="margin:8px 0 0;padding-left:18px;">' + list + '</ul>'
        : '<strong>' + message + '</strong>';
    } else {
      feedback.textContent = message || '';
    }
  }

  /* ── Loading state ── */
  function setLoading(submit, loading) {
    if (!submit) return;
    if (loading) {
      submit.dataset.originalText = submit.tagName.toLowerCase() === 'button' ? submit.textContent : submit.value;
      if (submit.tagName.toLowerCase() === 'button') {
        submit.textContent = 'Traitement...';
      } else {
        submit.value = 'Traitement...';
      }
      submit.disabled = true;
    } else {
      var original = submit.dataset.originalText;
      if (original) {
        if (submit.tagName.toLowerCase() === 'button') {
          submit.textContent = original;
        } else {
          submit.value = original;
        }
      }
      submit.disabled = false;
    }
  }

  /* ── Confirm modal ── */
  var modal = document.getElementById('confirm-modal');
  var modalMessage = document.getElementById('confirm-message');
  var modalOk = document.getElementById('confirm-ok');
  var modalCancel = document.getElementById('confirm-cancel');
  var pendingAction = null;

  function closeModal() {
    if (!modal) return;
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    pendingAction = null;
  }

  if (modalCancel) {
    modalCancel.addEventListener('click', closeModal);
  }

  if (modal) {
    modal.addEventListener('click', function (event) {
      if (event.target === modal) closeModal();
    });
  }

  if (modalOk) {
    modalOk.addEventListener('click', function () {
      if (!pendingAction) return closeModal();
      var action = pendingAction;
      closeModal();
      if (action.type === 'link') {
        window.location.href = action.href;
      } else if (action.type === 'submit') {
        if (typeof action.form.requestSubmit === 'function') {
          action.form.requestSubmit(action.submitter || undefined);
        } else if (action.submitter) {
          action.submitter.click();
        } else {
          action.form.submit();
        }
      }
    });
  }

  /* ── Set up confirm triggers ── */
  document.querySelectorAll('[data-confirm-message]').forEach(function (element) {
    element.addEventListener('click', function (event) {
      event.preventDefault();
      var message = this.getAttribute('data-confirm-message') || 'Confirmer cette action ?';
      if (modalMessage) modalMessage.textContent = message;
      if (modal) {
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
      }
      if (this.tagName.toLowerCase() === 'a') {
        pendingAction = { type: 'link', href: this.getAttribute('href') };
      } else if (this.type === 'submit' && this.form) {
        pendingAction = { type: 'submit', form: this.form, submitter: this };
      }
    });
  });

  /* ── AJAX form handling ── */
  document.querySelectorAll('form').forEach(function (form) {
    if (form.dataset.ajaxForm !== 'true') {
      form.addEventListener('submit', function () {
        var submit = this.querySelector('button[type="submit"], input[type="submit"]');
        if (!submit) return;
        var original = submit.dataset.originalText || submit.textContent || submit.value || 'Envoyer';
        submit.dataset.originalText = original;
        if (submit.tagName.toLowerCase() === 'button') {
          submit.textContent = 'Traitement...';
        } else {
          submit.value = 'Traitement...';
        }
        submit.disabled = true;
      });
      return;
    }

    form.addEventListener('submit', async function (event) {
      event.preventDefault();

      var submit = form.querySelector('button[type="submit"], input[type="submit"]');
      resetFieldErrors(form);
      setFeedback(form, 'error', '', {});
      setLoading(submit, true);

      try {
        var response = await fetch(form.action, {
          method: (form.method || 'POST').toUpperCase(),
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: new FormData(form),
        });

        var data = await response.json();

        if (!response.ok || !data.success) {
          setFeedback(form, 'error', data.message || 'Veuillez verifier les champs du formulaire.', data.errors || {});
          var errors = data.errors || {};
          var messages = Object.values(errors).map(function (v) { return String(v); });
          var allSameAsTop = messages.length > 0 && messages.every(function (m) { return m === String(data.message || ''); });
          if (!allSameAsTop) {
            showFieldErrors(form, errors);
          }
          setLoading(submit, false);
          return;
        }

        if (data.message) {
          setFeedback(form, 'success', data.message);
        }

        if (data.redirect) {
          window.location.href = data.redirect;
          return;
        }

        setLoading(submit, false);
      } catch (error) {
        setFeedback(form, 'error', 'Une erreur est survenue. Merci de reessayer.', {});
        setLoading(submit, false);
      }
    });
  });

  /* ── Flash auto-dismiss ── */
  document.querySelectorAll('.alert, .flash').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity 0.3s ease';
      el.style.opacity = '0';
      setTimeout(function () { el.remove(); }, 300);
    }, 5000);
  });
})();
