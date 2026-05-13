/* ==========================================================
   AUTH JS — auth.js
   Registration multi-step, password toggle, email check, IMC preview.
   ========================================================== */

(function () {
  'use strict';

  /* ── Helper: set field validation state ── */
  function setFieldState(form, name, ok, message) {
    message = message || '';
    var input = form.querySelector('[name="' + name + '"]');
    var icon = form.querySelector('[data-icon="' + name + '"]');
    var err = form.querySelector('[data-field-error="' + name + '"]');
    if (!input || !icon || !err) return;
    if (String(input.value || '').trim() === '') {
      icon.className = 'field-icon';
      input.classList.remove('is-invalid', 'is-valid');
      err.textContent = '';
      return;
    }
    icon.className = 'field-icon ' + (ok ? 'ok' : 'err');
    input.classList.toggle('is-invalid', !ok);
    input.classList.toggle('is-valid', !!ok);
    err.textContent = ok ? '' : message;
  }

  /* ── Password toggle (generic) ── */
  document.querySelectorAll('.eye-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = this.closest('.field-wrap').querySelector('input[type="password"], input[type="text"]');
      if (!input) return;
      var hidden = input.type === 'password';
      input.type = hidden ? 'text' : 'password';
      var img = this.querySelector('img');
      if (img) {
        var src = img.src;
        img.src = hidden ? src.replace('eye.svg', 'eye-off.svg') : src.replace('eye-off.svg', 'eye.svg');
      }
    });
  });

  /* ── Register personal (step 1) ── */
  var personalForm = document.getElementById('register-personal-form');
  if (personalForm) {
    var checkEmailUrl = personalForm.dataset.checkEmailUrl || '';
    var emailInput = personalForm.querySelector('#email');
    var passwordInput = personalForm.querySelector('#mot_de_passe');

    personalForm.querySelector('#nom').addEventListener('blur', function (e) {
      var v = e.target.value.trim();
      setFieldState(personalForm, 'nom', v.length >= 2 && v.length <= 100, 'Le nom doit contenir entre 2 et 100 caracteres.');
    });

    personalForm.querySelector('#date_naissance').addEventListener('blur', function (e) {
      var v = e.target.value;
      setFieldState(personalForm, 'date_naissance', v === '' || /^\d{4}-\d{2}-\d{2}$/.test(v), 'Date invalide.');
    });

    emailInput.addEventListener('blur', async function () {
      var v = emailInput.value.trim();
      if (!v) return setFieldState(personalForm, 'email', false, 'Email requis.');
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) return setFieldState(personalForm, 'email', false, 'Format invalide.');
      if (!checkEmailUrl) return;
      try {
        var res = await fetch(checkEmailUrl, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: new URLSearchParams({ email: v })
        });
        var data = await res.json();
        setFieldState(personalForm, 'email', !!data.available, data.message || 'Email indisponible.');
      } catch (_) {
        setFieldState(personalForm, 'email', false, 'Verification impossible.');
      }
    });

    var updateStrength = function () {
      var v = passwordInput.value;
      var ok = v.length >= 8 && /[A-Z]/.test(v) && /\d/.test(v);
      var score = [v.length >= 8, /[A-Z]/.test(v), /\d/.test(v)].filter(Boolean).length;
      var strengthEl = document.getElementById('password-strength');
      if (strengthEl) strengthEl.textContent = 'Force: ' + ['Faible', 'Moyenne', 'Bonne', 'Forte'][score];
      setFieldState(personalForm, 'mot_de_passe', ok, 'Le mot de passe doit contenir au moins 8 caracteres, une majuscule et un chiffre.');
    };
    passwordInput.addEventListener('input', updateStrength);
    passwordInput.addEventListener('blur', updateStrength);
  }

  /* ── Register health (step 2-3) ── */
  var healthForm = document.getElementById('register-health-form');
  if (healthForm) {
    var imcPreviewUrl = healthForm.dataset.imcPreviewUrl || '';
    var step2 = document.getElementById('step-2');
    var step3 = document.getElementById('step-3');
    var stepIndicator = document.getElementById('step-indicator');
    var stepTitle = document.getElementById('step-title');
    var stepProgress = document.getElementById('step-progress');
    var taille = healthForm.querySelector('#taille_cm');
    var poids = healthForm.querySelector('#poids_kg');
    var poidsObjectif = healthForm.querySelector('#poids_objectif');
    var poidsWrap = document.getElementById('poids-objectif-wrap');
    var currentImc = null;
    var currentImcLabel = null;

    function objectifRadios() {
      return Array.from(healthForm.querySelectorAll('input[name="id_objectif"]'));
    }

    function selectedObjectif() {
      return objectifRadios().find(function (r) { return r.checked; }) || null;
    }

    function requiresTarget() {
      var s = selectedObjectif();
      if (!s) return false;
      var l = s.closest('label').innerText.toLowerCase();
      return l.includes('perte') || l.includes('prise');
    }

    function validatePoidsObjectif() {
      if (!requiresTarget()) return true;
      var p = parseFloat(poids.value || '0');
      var o = parseFloat(poidsObjectif.value || '0');
      var l = (selectedObjectif() && selectedObjectif().closest('label').innerText.toLowerCase()) || '';
      if (!o || o <= 0) { setFieldState(healthForm, 'poids_objectif', false, 'Poids cible requis.'); return false; }
      if (l.includes('perte') && o >= p) { setFieldState(healthForm, 'poids_objectif', false, 'Le poids cible doit etre inferieur au poids actuel.'); return false; }
      if (l.includes('prise') && o <= p) { setFieldState(healthForm, 'poids_objectif', false, 'Le poids cible doit etre superieur au poids actuel.'); return false; }
      setFieldState(healthForm, 'poids_objectif', true);
      return true;
    }

    function toggleTarget() {
      var show = requiresTarget();
      poidsWrap.style.display = show ? 'block' : 'none';
      poidsObjectif.required = show;
      if (!show) {
        poidsObjectif.value = '';
        setFieldState(healthForm, 'poids_objectif', true);
      }
    }

    var imcTimer = null;

    async function computeImc() {
      var t = parseFloat(taille.value || '0');
      var p = parseFloat(poids.value || '0');
      if (!(t > 0 && p > 0) || !imcPreviewUrl) return;
      try {
        var res = await fetch(imcPreviewUrl, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: new URLSearchParams({ taille_cm: String(t), poids_kg: String(p) })
        });
        var data = await res.json();
        if (!data.success) return;
        currentImc = data.imc;
        currentImcLabel = data.label || '-';
        document.getElementById('imc-value').textContent = String(data.imc);
        document.getElementById('imc-label').textContent = currentImcLabel;
        var percent = Math.max(0, Math.min(100, (Number(data.imc) / 40) * 100));
        var ptr = document.getElementById('imc-pointer');
        ptr.style.width = percent + '%';
        ptr.style.backgroundColor = data.is_ideal ? '#12b76a' : (Number(data.imc) < 18.5 ? '#f59e0b' : '#ef4444');
        var msg = document.getElementById('imc-ideal-message');
        msg.style.display = data.is_ideal ? 'block' : 'none';

        objectifRadios().forEach(function (r) {
          var isIdeal = r.closest('label').dataset.objectifLabel.includes('ideal');
          if (!isIdeal) return;
          r.disabled = !!data.is_ideal;
          r.closest('label').classList.toggle('is-disabled', !!data.is_ideal);
          if (data.is_ideal && r.checked) r.checked = false;
        });
        toggleTarget();
      } catch (_) { }
    }

    function scheduleImc() {
      clearTimeout(imcTimer);
      imcTimer = setTimeout(computeImc, 180);
    }

    ['input', 'blur'].forEach(function (evt) {
      taille.addEventListener(evt, function () {
        var v = parseFloat(taille.value || '0');
        setFieldState(healthForm, 'taille_cm', v >= 50 && v <= 260, 'Entrez une taille valide (ex: 175).');
        scheduleImc();
      });
      poids.addEventListener(evt, function () {
        var v = parseFloat(poids.value || '0');
        setFieldState(healthForm, 'poids_kg', v >= 20 && v <= 350, 'Entrez un poids valide (ex: 72).');
        scheduleImc();
      });
    });

    poidsObjectif.addEventListener('blur', validatePoidsObjectif);
    objectifRadios().forEach(function (r) {
      r.addEventListener('change', function () { toggleTarget(); validatePoidsObjectif(); });
    });

    document.getElementById('to-recap').addEventListener('click', function () {
      var ok = true;
      var objectifSection = document.getElementById('objectif-section');
      var t = parseFloat(taille.value || '0');
      var p = parseFloat(poids.value || '0');
      if (!(t >= 50 && t <= 260)) { setFieldState(healthForm, 'taille_cm', false, 'Entrez une taille valide (ex: 175).'); ok = false; }
      if (!(p >= 20 && p <= 350)) { setFieldState(healthForm, 'poids_kg', false, 'Entrez un poids valide (ex: 72).'); ok = false; }
      if (!selectedObjectif()) {
        healthForm.querySelector('[data-field-error="id_objectif"]').textContent = 'Choisissez un objectif.';
        objectifSection.classList.add('invalid-section');
        ok = false;
      } else {
        healthForm.querySelector('[data-field-error="id_objectif"]').textContent = '';
        objectifSection.classList.remove('invalid-section');
      }
      var targetOk = validatePoidsObjectif();
      if (!targetOk) ok = false;
      if (poidsWrap) poidsWrap.classList.toggle('invalid-section', !!requiresTarget() && !targetOk);
      if (!ok) return;

      document.getElementById('r-taille').textContent = taille.value + ' cm';
      document.getElementById('r-poids').textContent = poids.value + ' kg';
      document.getElementById('r-imc').textContent = currentImc ? currentImc + ' (' + (currentImcLabel || '-') + ')' : '-';
      document.getElementById('r-objectif').textContent = (selectedObjectif() && selectedObjectif().closest('label').innerText.trim()) || '-';
      document.getElementById('r-poids-objectif').textContent = poidsObjectif.value ? poidsObjectif.value + ' kg' : 'Non renseigne';

      step2.style.display = 'none';
      step3.style.display = 'block';
      stepIndicator.textContent = 'Etape 3/3';
      stepTitle.textContent = 'Recapitulatif';
      stepProgress.style.width = '100%';
    });

    document.getElementById('back-to-health').addEventListener('click', function () {
      step3.style.display = 'none';
      step2.style.display = 'block';
      stepIndicator.textContent = 'Etape 2/3';
      stepTitle.textContent = 'Informations sante';
      stepProgress.style.width = '66.66%';
    });

    toggleTarget();
  }

  /* ── Admin login ── */
  var adminLoginForm = document.getElementById('admin-login-form');
  if (adminLoginForm) {
    var toggleBtn = document.getElementById('togglePassword');
    if (toggleBtn) {
      toggleBtn.addEventListener('click', function () {
        var input = document.getElementById('mot_de_passe');
        var img = this.querySelector('img');
        var hidden = input.type === 'password';
        input.type = hidden ? 'text' : 'password';
        if (img) {
          img.src = hidden ? img.src.replace('eye.svg', 'eye-off.svg') : img.src.replace('eye-off.svg', 'eye.svg');
        }
      });
    }

    adminLoginForm.addEventListener('submit', function () {
      var btnText = document.getElementById('btnText');
      var submitBtn = adminLoginForm.querySelector('.btn');
      if (btnText) btnText.textContent = 'Connexion en cours...';
      if (submitBtn) submitBtn.disabled = true;
    });

    var emailField = document.getElementById('email');
    if (emailField) emailField.focus();
  }
})();
