/* ==========================================================
   REGIME JS — regime.js
   Estimate calculations, objective status for regime show page.
   ========================================================== */

(function () {
  'use strict';

  var form = document.getElementById('commande-form');
  var regimeData = window.regimeData || null;

  if (form && regimeData) {
    var objectiveStatus = document.getElementById('objectif-status');
    var objectiveStatusFail = document.getElementById('objectif-status-fail');

    var variationMonthly = Number(regimeData.variationMonthly || 0);
    var user = regimeData.user || null;
    var imcIdealMin = regimeData.imcIdealMin ?? null;
    var imcIdealMax = regimeData.imcIdealMax ?? null;

    function formatKg(value) {
      var sign = value > 0 ? '+' : '';
      var formatted = value.toFixed(2).replace(/\.00$/, '').replace(/\.([1-9])0$/, '.$1');
      return sign + formatted + 'kg';
    }

    function updateEstimates() {
      var estimateNodes = form.querySelectorAll('.estimate');
      estimateNodes.forEach(function (node) {
        var days = Number(node.dataset.days || 0);
        var estimated = variationMonthly * (days / 30);
        node.textContent = formatKg(estimated);
      });
    }

    function isObjectiveReached(selectedDays) {
      if (!user) return false;

      var variation = variationMonthly * (selectedDays / 30);
      var poidsActuel = Number(user.poids_kg || 0);
      var poidsObjectif = user.poids_objectif !== null ? Number(user.poids_objectif) : null;
      var tailleCm = Number(user.taille_cm || 0);
      var objectifId = Number(user.id_objectif || 0);

      if (objectifId === 1 && poidsObjectif !== null) {
        var cible = poidsObjectif - poidsActuel;
        return variation <= cible;
      }

      if (objectifId === 2 && poidsObjectif !== null) {
        var cible2 = poidsObjectif - poidsActuel;
        return variation >= cible2;
      }

      if (objectifId === 3 && tailleCm > 0 && imcIdealMin !== null && imcIdealMax !== null) {
        var tailleM = tailleCm / 100;
        var nouveauPoids = poidsActuel + variation;
        var imc = nouveauPoids / (tailleM * tailleM);
        return imc >= imcIdealMin && imc <= imcIdealMax;
      }

      return false;
    }

    function updateObjectiveStatus() {
      var selected = form.querySelector('input[name="id_duree_regime"]:checked');
      if (!selected) return;

      var days = Number(selected.dataset.days || 0);

      if (!user) {
        if (objectiveStatus) objectiveStatus.style.display = 'none';
        if (objectiveStatusFail) objectiveStatusFail.style.display = 'none';
        return;
      }

      var reached = isObjectiveReached(days);
      if (objectiveStatus) objectiveStatus.style.display = reached ? 'block' : 'none';
      if (objectiveStatusFail) objectiveStatusFail.style.display = reached ? 'none' : 'block';
    }

    updateEstimates();
    updateObjectiveStatus();
    form.addEventListener('change', updateObjectiveStatus);
  }

  var listData = window.regimeListData || null;
  var filters = document.getElementById('filters');
  var rows = document.getElementById('regime-rows');

  if (filters && rows && listData) {
    var detailBase = listData.detailBase || '';
    var eyeIcon = listData.eyeIcon || '';

    var renderRows = function (regimes, regimeDurees) {
      rows.innerHTML = '';

      if (!regimes.length) {
        var emptyRow = document.createElement('tr');
        var emptyCell = document.createElement('td');
        emptyCell.colSpan = 6;
        emptyCell.className = 'empty';
        emptyCell.textContent = 'Aucun régime disponible.';
        emptyRow.appendChild(emptyCell);
        rows.appendChild(emptyRow);
        return;
      }

      regimes.forEach(function (regime) {
        var row = document.createElement('tr');

        var nameCell = document.createElement('td');
        var nameText = document.createElement('span');
        nameText.className = 'regime-name';
        nameText.textContent = regime.nom_regime;
        nameCell.appendChild(nameText);
        row.appendChild(nameCell);

        var variationCell = document.createElement('td');
        var variationBadge = document.createElement('span');
        variationBadge.className = 'badge';
        variationBadge.textContent = regime.variation_label;
        variationCell.appendChild(variationBadge);
        row.appendChild(variationCell);

        var compositionCell = document.createElement('td');
        var compositionWrap = document.createElement('div');
        compositionWrap.className = 'composition-wrap';
        var composition = document.createElement('div');
        composition.className = 'composition-chart composition-mini';
        composition.setAttribute('data-tooltip', regime.composition_tooltip || '');
        composition.style.setProperty('--pie-gradients', regime.composition_gradient || '#e9eef3 0% 100%');
        var tooltip = document.createElement('span');
        tooltip.className = 'composition-tooltip';
        composition.appendChild(tooltip);
        compositionWrap.appendChild(composition);

        var legend = document.createElement('div');
        legend.className = 'composition-legend-inline';
        (regime.composition_legend || []).forEach(function (item) {
          var entry = document.createElement('span');
          entry.className = 'composition-legend-item';

          var dot = document.createElement('span');
          dot.className = 'legend-dot';
          dot.style.background = item.color || '#e9eef3';
          entry.appendChild(dot);

          var label = document.createElement('span');
          label.textContent = item.label + ' ' + item.value_label + '%';
          entry.appendChild(label);

          legend.appendChild(entry);
        });
        compositionWrap.appendChild(legend);
        compositionCell.appendChild(compositionWrap);
        row.appendChild(compositionCell);

        var durationsCell = document.createElement('td');
        var durees = (regimeDurees && regimeDurees[regime.id_regime]) || [];
        if (durees.length) {
          var group = document.createElement('div');
          group.className = 'badge-group';
          durees.forEach(function (duree) {
            var badge = document.createElement('span');
            badge.className = 'badge badge-muted';
            badge.textContent = duree + ' j';
            group.appendChild(badge);
          });
          durationsCell.appendChild(group);
        } else {
          var badgeEmpty = document.createElement('span');
          badgeEmpty.className = 'badge badge-muted';
          badgeEmpty.textContent = 'Aucune';
          durationsCell.appendChild(badgeEmpty);
        }
        row.appendChild(durationsCell);

        var countCell = document.createElement('td');
        var countBadge = document.createElement('span');
        countBadge.className = 'badge badge-muted';
        countBadge.textContent = regime.activity_count || 0;
        countCell.appendChild(countBadge);
        row.appendChild(countCell);

        var actionCell = document.createElement('td');
        var actionLink = document.createElement('a');
        actionLink.href = detailBase + '/' + regime.id_regime;
        actionLink.className = 'btn btn-ghost btn-icon';
        actionLink.title = 'Voir le détail';
        var eye = document.createElement('img');
        eye.src = eyeIcon;
        eye.alt = 'Voir';
        actionLink.appendChild(eye);
        actionCell.appendChild(actionLink);
        row.appendChild(actionCell);
        rows.appendChild(row);
      });

      if (window.initCompositionTooltips) {
        window.initCompositionTooltips(rows);
      }
    };

    var applyFilters = function () {
      var formData = new FormData(filters);
      var params = new URLSearchParams(formData);

      fetch('/regimes?' + params.toString(), {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
        .then(function (response) { return response.json(); })
        .then(function (data) {
          renderRows(data.regimes || [], data.regimeDurees || {});
        })
        .catch(function () {
          renderRows([], {});
        });
    };

    filters.addEventListener('change', applyFilters);
  }
})();
