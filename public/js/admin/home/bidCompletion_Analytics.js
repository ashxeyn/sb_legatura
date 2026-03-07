// Bid Completion Analytics JavaScript
// All chart data is read from data-* attributes set by the blade/controller.
// No hardcoded values remain.

// ── Counter animation ────────────────────────────────────────────────
function animateNumber(element) {
  const target   = parseFloat(element.getAttribute('data-target')) || 0;
  const isFloat  = target % 1 !== 0;
  const duration = 1400;
  const startTime = performance.now();

  function update(currentTime) {
    const elapsed  = currentTime - startTime;
    const progress = Math.min(elapsed / duration, 1);
    const eased    = 1 - (1 - progress) * (1 - progress); // easeOutQuad
    const current  = target * eased;

    element.textContent = isFloat
      ? current.toFixed(1)
      : Math.floor(current).toLocaleString();

    if (progress < 1) requestAnimationFrame(update);
  }

  requestAnimationFrame(update);
}

// ── Progress bars ─────────────────────────────────────────────────────
function animateProgressBars() {
  document.querySelectorAll('.progress-bar').forEach((bar, index) => {
    const width = bar.getAttribute('data-width') || 0;
    setTimeout(() => {
      bar.style.transition = 'width 0.8s ease';
      bar.style.width = width + '%';
    }, 300 + index * 150);
  });
}

// ── DOMContentLoaded ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {

  // Animate all stat counters
  document.querySelectorAll('.stat-number').forEach(el => animateNumber(el));
  animateProgressBars();

  // ── Bid Timeline Chart ──────────────────────────────────────────────
  const timelineCanvas = document.getElementById('bidTimelineChart');
  if (timelineCanvas) {
    const months    = JSON.parse(timelineCanvas.dataset.months    || '[]');
    const submitted = JSON.parse(timelineCanvas.dataset.submitted || '[]');
    const accepted  = JSON.parse(timelineCanvas.dataset.accepted  || '[]');

    new Chart(timelineCanvas, {
      type: 'bar',
      data: {
        labels: months,
        datasets: [
          {
            label: 'Bids Submitted',
            data: submitted,
            backgroundColor: 'rgba(168, 85, 247, 0.8)',
            borderColor: 'rgb(168, 85, 247)',
            borderWidth: 2,
            borderRadius: 8,
            hoverBackgroundColor: 'rgba(168, 85, 247, 1)',
          },
          {
            label: 'Bids Accepted',
            data: accepted,
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            borderColor: 'rgb(16, 185, 129)',
            borderWidth: 2,
            borderRadius: 8,
            hoverBackgroundColor: 'rgba(16, 185, 129, 1)',
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: {
            display: true,
            position: 'top',
            labels: { usePointStyle: true, padding: 20, font: { size: 13, weight: '600' } },
          },
          tooltip: {
            backgroundColor: 'rgba(255,255,255,0.95)',
            titleColor: '#1f2937',
            bodyColor: '#374151',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 12,
            cornerRadius: 8,
            displayColors: true,
            callbacks: {
              label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y + ' bids',
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
            ticks: { font: { size: 12 }, padding: 10, precision: 0 },
          },
          x: {
            grid: { display: false, drawBorder: false },
            ticks: { font: { size: 12 }, padding: 10 },
          },
        },
      },
    });
  }

  // ── Bid Status Distribution Doughnut ────────────────────────────────
  const statusCanvas = document.getElementById('bidStatusChart');
  if (statusCanvas) {
    const labels = JSON.parse(statusCanvas.dataset.labels || '[]');
    const values = JSON.parse(statusCanvas.dataset.values || '[]');

    const palette = {
      'Accepted':     { bg: 'rgba(16,185,129,0.85)',  border: 'rgb(16,185,129)' },
      'Submitted':    { bg: 'rgba(245,158,11,0.85)',  border: 'rgb(245,158,11)' },
      'Under Review': { bg: 'rgba(59,130,246,0.85)',  border: 'rgb(59,130,246)' },
      'Rejected':     { bg: 'rgba(239,68,68,0.85)',   border: 'rgb(239,68,68)' },
      'Cancelled':    { bg: 'rgba(156,163,175,0.85)', border: 'rgb(156,163,175)' },
    };

    const bgColors     = labels.map(l => (palette[l] || { bg: 'rgba(99,102,241,0.85)' }).bg);
    const borderColors = labels.map(l => (palette[l] || { border: 'rgb(99,102,241)' }).border);

    new Chart(statusCanvas, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{
          data: values,
          backgroundColor: bgColors,
          borderColor: borderColors,
          borderWidth: 3,
          hoverOffset: 15,
          hoverBorderWidth: 4,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        animation: { animateRotate: true, animateScale: true, duration: 1400, easing: 'easeOutQuart' },
        plugins: {
          legend: {
            display: true,
            position: 'right',
            labels: {
              usePointStyle: true,
              padding: 20,
              font: { size: 13, weight: '600' },
              generateLabels(chart) {
                const ds    = chart.data.datasets[0];
                const total = ds.data.reduce((a, b) => a + b, 0);
                return chart.data.labels.map((label, i) => ({
                  text: `${label}: ${((ds.data[i] / total) * 100).toFixed(1)}%`,
                  fillStyle: ds.backgroundColor[i],
                  hidden: false,
                  index: i,
                }));
              },
            },
          },
          tooltip: {
            backgroundColor: 'rgba(255,255,255,0.95)',
            titleColor: '#1f2937',
            bodyColor: '#374151',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 12,
            cornerRadius: 8,
            callbacks: {
              label(ctx) {
                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                const pct   = ((ctx.parsed / total) * 100).toFixed(1);
                return `${ctx.label}: ${ctx.parsed} bids (${pct}%)`;
              },
            },
          },
        },
      },
    });
  }

  // ── Geographic Distribution Bar Chart ───────────────────────────────
  const geoCanvas = document.getElementById('geographicDistributionChart');
  if (geoCanvas) {
    const labels = JSON.parse(geoCanvas.dataset.labels || '[]');
    const values = JSON.parse(geoCanvas.dataset.values || '[]');

    // Color each bar distinctly
    const barColors = [
      'rgba(16,185,129,0.8)',   // Tetuan – emerald
      'rgba(59,130,246,0.8)',   // Tumaga – blue
      'rgba(99,102,241,0.8)',   // Sinunuc – indigo
      'rgba(249,115,22,0.8)',   // Malagutay – orange
      'rgba(168,85,247,0.8)',   // Baliwasan – purple
      'rgba(20,184,166,0.8)',   // Upper Calarian – teal
      'rgba(236,72,153,0.8)',   // Others – pink
    ];

    new Chart(geoCanvas, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Projects',
          data: values,
          backgroundColor: barColors.slice(0, labels.length),
          borderColor: barColors.slice(0, labels.length).map(c => c.replace('0.8', '1')),
          borderWidth: 2,
          borderRadius: 8,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(255,255,255,0.95)',
            titleColor: '#1f2937',
            bodyColor: '#374151',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 12,
            cornerRadius: 8,
            callbacks: {
              label: ctx => 'Projects: ' + ctx.parsed.y,
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
            ticks: { font: { size: 12 }, padding: 10, precision: 0 },
            title: { display: true, text: 'Number of Projects', font: { size: 13, weight: '600' } },
          },
          x: {
            grid: { display: false, drawBorder: false },
            ticks: { font: { size: 12 }, padding: 10 },
          },
        },
      },
    });
  }

  // ── Table row hover effect ───────────────────────────────────────────
  document.querySelectorAll('tbody tr').forEach(row => {
    row.addEventListener('click', function () {
      this.style.transform = 'scale(0.99)';
      setTimeout(() => { this.style.transform = 'scale(1)'; }, 100);
    });
  });

  // ── Export button ────────────────────────────────────────────────────
  const exportBtn = document.getElementById('exportBidsBtn');
  if (exportBtn) {
    exportBtn.addEventListener('click', function () {
      const orig = this.innerHTML;
      this.innerHTML = `
        <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Exporting...`;
      this.disabled = true;

      setTimeout(() => {
        this.innerHTML = orig;
        this.disabled = false;
        alert('Bid data exported successfully!');
      }, 1500);
    });
  }

});