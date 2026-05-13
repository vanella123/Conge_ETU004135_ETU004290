/* ==========================================================
   CHARTS JS — charts.js
   Pie chart and revenue trend chart rendering.
   Data must be passed from controller via json_encode.
   ========================================================== */

/**
 * Render a CSS conic-gradient pie chart.
 * @param {Object} config
 * @param {string} config.containerId - ID of the .pie-chart-container element
 * @param {Array} config.data - Array of {label, total, color}
 */
function renderPieChart(config) {
  var container = document.getElementById(config.containerId);
  if (!container) return;

  var data = config.data || [];
  var totalAll = data.reduce(function (sum, d) { return sum + Number(d.total || 0); }, 0);

  if (totalAll === 0) {
    container.style.setProperty('--pie-gradients', '#e9eef3 0% 100%');
    return;
  }

  var gradients = [];
  var cumulative = 0;

  data.forEach(function (d) {
    var percent = (Number(d.total) / totalAll) * 100;
    if (percent === 0) return;
    var next = cumulative + percent;
    gradients.push(d.color + ' ' + cumulative + '% ' + next + '%');
    cumulative = next;
  });

  container.style.setProperty('--pie-gradients', gradients.join(', '));

  // Build legend
  var legendEl = container.querySelector('.pie-legend');
  if (legendEl) {
    legendEl.innerHTML = '';
    data.forEach(function (d) {
      if (Number(d.total) === 0) return;
      var item = document.createElement('div');
      item.className = 'legend-item';
      item.innerHTML =
        '<span class="legend-color" style="background:' + d.color + ';"></span>' +
        '<span>' + d.label + ' (' + d.total + ')</span>';
      legendEl.appendChild(item);
    });
  }
}

/**
 * Render a simple line/bar trend chart using canvas.
 * @param {Object} config
 * @param {string} config.canvasId - ID of the canvas element
 * @param {Array} config.labels - X-axis labels (dates)
 * @param {Array} config.values - Y-axis values (amounts)
 * @param {string} [config.color] - Line color
 */
function renderTrendChart(config) {
  var canvas = document.getElementById(config.canvasId);
  if (!canvas || !canvas.getContext) return;

  var ctx = canvas.getContext('2d');
  var labels = config.labels || [];
  var values = config.values || [];
  var color = config.color || '#1f8f6a';

  if (labels.length === 0) return;

  // Setup canvas dimensions
  var dpr = window.devicePixelRatio || 1;
  var rect = canvas.getBoundingClientRect();
  canvas.width = rect.width * dpr;
  canvas.height = rect.height * dpr;
  ctx.scale(dpr, dpr);

  var w = rect.width;
  var h = rect.height;
  var padding = { top: 20, right: 20, bottom: 40, left: 60 };
  var chartW = w - padding.left - padding.right;
  var chartH = h - padding.top - padding.bottom;

  var maxVal = Math.max.apply(null, values) || 1;
  maxVal = Math.ceil(maxVal / 1000) * 1000; // Round up

  // Clear
  ctx.clearRect(0, 0, w, h);

  // Grid lines
  ctx.strokeStyle = '#e2e8f0';
  ctx.lineWidth = 1;
  var gridLines = 5;
  for (var i = 0; i <= gridLines; i++) {
    var y = padding.top + (chartH / gridLines) * i;
    ctx.beginPath();
    ctx.moveTo(padding.left, y);
    ctx.lineTo(w - padding.right, y);
    ctx.stroke();

    // Y labels
    var val = maxVal - (maxVal / gridLines) * i;
    ctx.fillStyle = '#64748b';
    ctx.font = '11px Inter, sans-serif';
    ctx.textAlign = 'right';
    ctx.fillText(val.toLocaleString('fr-FR'), padding.left - 8, y + 4);
  }

  // Plot points and line
  var points = [];
  values.forEach(function (v, idx) {
    var x = padding.left + (chartW / (labels.length - 1 || 1)) * idx;
    var y = padding.top + chartH - (v / maxVal) * chartH;
    points.push({ x: x, y: y });
  });

  // Fill area
  ctx.beginPath();
  ctx.moveTo(points[0].x, padding.top + chartH);
  points.forEach(function (p) { ctx.lineTo(p.x, p.y); });
  ctx.lineTo(points[points.length - 1].x, padding.top + chartH);
  ctx.closePath();
  var gradient = ctx.createLinearGradient(0, padding.top, 0, padding.top + chartH);
  gradient.addColorStop(0, color + '33');
  gradient.addColorStop(1, color + '05');
  ctx.fillStyle = gradient;
  ctx.fill();

  // Line
  ctx.beginPath();
  points.forEach(function (p, idx) {
    if (idx === 0) ctx.moveTo(p.x, p.y);
    else ctx.lineTo(p.x, p.y);
  });
  ctx.strokeStyle = color;
  ctx.lineWidth = 2.5;
  ctx.lineJoin = 'round';
  ctx.stroke();

  // Dots
  points.forEach(function (p) {
    ctx.beginPath();
    ctx.arc(p.x, p.y, 4, 0, Math.PI * 2);
    ctx.fillStyle = color;
    ctx.fill();
    ctx.beginPath();
    ctx.arc(p.x, p.y, 2, 0, Math.PI * 2);
    ctx.fillStyle = '#fff';
    ctx.fill();
  });

  // X labels
  ctx.fillStyle = '#64748b';
  ctx.font = '11px Inter, sans-serif';
  ctx.textAlign = 'center';
  labels.forEach(function (label, idx) {
    var x = padding.left + (chartW / (labels.length - 1 || 1)) * idx;
    ctx.fillText(label, x, h - 10);
  });
}
