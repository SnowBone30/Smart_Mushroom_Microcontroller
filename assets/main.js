// ── PITAHAYA FARM — SHARED JS ──────────────────────────────────────────────

// Toggle device state label
function toggleDevice(device) {
  const toggle = document.getElementById(device + '-toggle');
  const state  = document.getElementById(device + '-state');
  if (!toggle || !state) return;
  if (toggle.checked) {
    state.textContent = '[ON]';
    state.className = 'override-state text-green';
  } else {
    state.textContent = '[OFF]';
    state.className = 'override-state text-dim';
  }
}

// Mode toggle
function setMode(btn) {
  document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}

// Scroll-reveal for .reveal elements
const revealObserver = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.classList.add('visible');
      revealObserver.unobserve(e.target);
    }
  });
}, { threshold: 0.08 });
document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

// Chart.js default shared config
const CHART_DEFAULTS = {
  responsive: true,
  maintainAspectRatio: false,
  animation: { duration: 1000, easing: 'easeInOutQuart' },
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#0d1424',
      borderColor: '#1e2d45',
      borderWidth: 1,
      titleColor: '#e8f0ff',
      bodyColor: '#6a82a0',
      titleFont: { family: 'Rajdhani', size: 13, weight: '700' },
      bodyFont:  { family: 'Share Tech Mono', size: 11 },
    }
  },
  scales: {
    x: {
      grid: { color: '#1a2535' },
      ticks: { color: '#4a6080', font: { family: 'Share Tech Mono', size: 10 } }
    },
    y: {
      grid: { color: '#1a2535' },
      ticks: { color: '#4a6080', font: { family: 'Share Tech Mono', size: 10 } }
    }
  }
};

// CO2 warning-line plugin
Chart.register({
  id: 'warningLine',
  afterDraw(chart) {
    if (!chart.canvas.dataset.warningLine) return;
    const val = parseFloat(chart.canvas.dataset.warningLine);
    const { ctx, chartArea: { left, right }, scales: { y } } = chart;
    const yPos = y.getPixelForValue(val);
    ctx.save();
    ctx.setLineDash([6, 4]);
    ctx.strokeStyle = '#ff4d4d';
    ctx.lineWidth = 1.5;
    ctx.globalAlpha = 0.75;
    ctx.beginPath(); ctx.moveTo(left, yPos); ctx.lineTo(right, yPos); ctx.stroke();
    ctx.font = '10px Share Tech Mono';
    ctx.fillStyle = '#ff4d4d';
    ctx.globalAlpha = 1;
    ctx.fillText('⚠ Warning ' + val, right - 90, yPos - 5);
    ctx.restore();
  }
});

// Gradient helper
function makeGradient(ctx, color, alpha1 = 0.35, alpha2 = 0.02) {
  const g = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
  g.addColorStop(0, color.replace(')', `,${alpha1})`).replace('rgb', 'rgba'));
  g.addColorStop(1, color.replace(')', `,${alpha2})`).replace('rgb', 'rgba'));
  return g;
}
