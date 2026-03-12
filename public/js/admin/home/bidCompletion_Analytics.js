// Bid Completion Analytics JavaScript
// All chart data is read from data-* attributes set by the blade/controller.
// Charts are destroyable/recreatable for AJAX date filter refresh.

// ── Counter animation ────────────────────────────────────────────────
function animateNumber(element) {
  const target   = parseFloat(element.getAttribute('data-target')) || 0;
  const isFloat  = target % 1 !== 0;
  const duration = 1400;
  const startTime = performance.now();

  function update(currentTime) {
    const elapsed  = currentTime - startTime;
    const progress = Math.min(elapsed / duration, 1);
    const eased    = 1 - (1 - progress) * (1 - progress);
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

// ── Chart instances ───────────────────────────────────────────────────
let bidTimelineChart = null;
let bidStatusChart   = null;
let geoChart         = null;

function buildTimelineChart(months, submitted, accepted) {
  const el = document.getElementById('bidTimelineChart');
  if (!el) return;
  if (bidTimelineChart) bidTimelineChart.destroy();
  bidTimelineChart = new Chart(el, {
    type: 'bar',
    data: {
      labels: months,
      datasets: [
        { label: 'Bids Submitted', data: submitted, backgroundColor: 'rgba(168,85,247,0.8)', borderColor: 'rgb(168,85,247)', borderWidth: 2, borderRadius: 8, hoverBackgroundColor: 'rgba(168,85,247,1)' },
        { label: 'Bids Accepted',  data: accepted,  backgroundColor: 'rgba(16,185,129,0.8)', borderColor: 'rgb(16,185,129)', borderWidth: 2, borderRadius: 8, hoverBackgroundColor: 'rgba(16,185,129,1)' },
      ],
    },
    options: {
      responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
      plugins: { legend: { display: true, position: 'top', labels: { usePointStyle: true, padding: 20, font: { size: 13, weight: '600' } } }, tooltip: { backgroundColor: 'rgba(255,255,255,0.95)', titleColor: '#1f2937', bodyColor: '#374151', borderColor: '#e5e7eb', borderWidth: 1, padding: 12, cornerRadius: 8, callbacks: { label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y + ' bids' } } },
      scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false }, ticks: { font: { size: 12 }, padding: 10, precision: 0 } }, x: { grid: { display: false, drawBorder: false }, ticks: { font: { size: 12 }, padding: 10 } } },
    },
  });
}

function buildStatusChart(labels, values) {
  const el = document.getElementById('bidStatusChart');
  if (!el) return;
  if (bidStatusChart) bidStatusChart.destroy();
  const palette = {
    'Accepted':     { bg: 'rgba(16,185,129,0.85)',  border: 'rgb(16,185,129)' },
    'Submitted':    { bg: 'rgba(245,158,11,0.85)',  border: 'rgb(245,158,11)' },
    'Under Review': { bg: 'rgba(59,130,246,0.85)',  border: 'rgb(59,130,246)' },
    'Rejected':     { bg: 'rgba(239,68,68,0.85)',   border: 'rgb(239,68,68)' },
    'Cancelled':    { bg: 'rgba(156,163,175,0.85)', border: 'rgb(156,163,175)' },
  };
  const bgColors     = labels.map(l => (palette[l] || { bg: 'rgba(99,102,241,0.85)' }).bg);
  const borderColors = labels.map(l => (palette[l] || { border: 'rgb(99,102,241)' }).border);
  bidStatusChart = new Chart(el, {
    type: 'doughnut',
    data: { labels, datasets: [{ data: values, backgroundColor: bgColors, borderColor: borderColors, borderWidth: 3, hoverOffset: 15, hoverBorderWidth: 4 }] },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '65%', animation: { animateRotate: true, animateScale: true, duration: 1400, easing: 'easeOutQuart' },
      plugins: {
        legend: { display: true, position: 'right', labels: { usePointStyle: true, padding: 20, font: { size: 13, weight: '600' }, generateLabels(chart) { const ds = chart.data.datasets[0]; const total = ds.data.reduce((a, b) => a + b, 0); return chart.data.labels.map((label, i) => ({ text: label + ': ' + ((ds.data[i] / total) * 100).toFixed(1) + '%', fillStyle: ds.backgroundColor[i], hidden: false, index: i })); } } },
        tooltip: { backgroundColor: 'rgba(255,255,255,0.95)', titleColor: '#1f2937', bodyColor: '#374151', borderColor: '#e5e7eb', borderWidth: 1, padding: 12, cornerRadius: 8, callbacks: { label(ctx) { const total = ctx.dataset.data.reduce((a, b) => a + b, 0); return ctx.label + ': ' + ctx.parsed + ' bids (' + ((ctx.parsed / total) * 100).toFixed(1) + '%)'; } } },
      },
    },
  });
}

function buildGeoChart(labels, values) {
  const el = document.getElementById('geographicDistributionChart');
  if (!el) return;
  if (geoChart) geoChart.destroy();
  const barColors = ['rgba(16,185,129,0.8)','rgba(59,130,246,0.8)','rgba(99,102,241,0.8)','rgba(249,115,22,0.8)','rgba(168,85,247,0.8)','rgba(20,184,166,0.8)','rgba(236,72,153,0.8)'];
  geoChart = new Chart(el, {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Projects', data: values, backgroundColor: barColors.slice(0, labels.length), borderColor: barColors.slice(0, labels.length).map(c => c.replace('0.8', '1')), borderWidth: 2, borderRadius: 8 }] },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(255,255,255,0.95)', titleColor: '#1f2937', bodyColor: '#374151', borderColor: '#e5e7eb', borderWidth: 1, padding: 12, cornerRadius: 8, callbacks: { label: ctx => 'Projects: ' + ctx.parsed.y } } },
      scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false }, ticks: { font: { size: 12 }, padding: 10, precision: 0 }, title: { display: true, text: 'Number of Projects', font: { size: 13, weight: '600' } } }, x: { grid: { display: false, drawBorder: false }, ticks: { font: { size: 12 }, padding: 10 } } },
    },
  });
}

// ── DOMContentLoaded ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {

  // Animate all stat counters
  document.querySelectorAll('.stat-number').forEach(el => animateNumber(el));
  animateProgressBars();

  // ── Build initial charts from data-* attributes ─────────────────────
  const timelineCanvas = document.getElementById('bidTimelineChart');
  if (timelineCanvas) {
    buildTimelineChart(
      JSON.parse(timelineCanvas.dataset.months || '[]'),
      JSON.parse(timelineCanvas.dataset.submitted || '[]'),
      JSON.parse(timelineCanvas.dataset.accepted || '[]')
    );
  }

  const statusCanvas = document.getElementById('bidStatusChart');
  if (statusCanvas) {
    buildStatusChart(
      JSON.parse(statusCanvas.dataset.labels || '[]'),
      JSON.parse(statusCanvas.dataset.values || '[]')
    );
  }

  const geoCanvas = document.getElementById('geographicDistributionChart');
  if (geoCanvas) {
    buildGeoChart(
      JSON.parse(geoCanvas.dataset.labels || '[]'),
      JSON.parse(geoCanvas.dataset.values || '[]')
    );
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
      this.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Exporting...';
      this.disabled = true;
      setTimeout(() => { this.innerHTML = orig; this.disabled = false; alert('Bid data exported successfully!'); }, 1500);
    });
  }

  // ═══════════════════════════════════════════════════════════════════
  // GLOBAL DATE FILTER — AJAX
  // ═══════════════════════════════════════════════════════════════════
  function getDateRange(preset) {
    const now = new Date();
    let from = '', to = now.toISOString().split('T')[0];
    switch (preset) {
      case 'last3months': { const d = new Date(now); d.setMonth(d.getMonth() - 3); from = d.toISOString().split('T')[0]; break; }
      case 'last6months': { const d = new Date(now); d.setMonth(d.getMonth() - 6); from = d.toISOString().split('T')[0]; break; }
      case 'thisyear':    from = now.getFullYear() + '-01-01'; break;
      case 'lastyear':    from = (now.getFullYear() - 1) + '-01-01'; to = (now.getFullYear() - 1) + '-12-31'; break;
      case 'all':         from = ''; to = ''; break;
    }
    return { from, to };
  }

  function esc(s) { return s ? String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;') : ''; }

  function refreshBidData(dateFrom, dateTo) {
    const loading = document.getElementById('filterLoading');
    if (loading) loading.classList.remove('hidden');

    const params = new URLSearchParams();
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo)   params.set('date_to', dateTo);

    fetch('/admin/analytics/bid-data?' + params.toString())
      .then(r => r.json())
      .then(d => {
        // Hero stat cards — update data-target then re-animate
        const heroCards = document.querySelectorAll('.stat-number');
        const heroTargets = [d.totalProjects, d.activeContractors, d.totalValueM, d.completionRate];
        heroCards.forEach((el, i) => {
          if (heroTargets[i] !== undefined) {
            el.setAttribute('data-target', heroTargets[i]);
            animateNumber(el);
          }
        });

        // Rebuild charts
        buildTimelineChart(d.timelineMonths, d.timelineSubmitted, d.timelineAccepted);
        buildStatusChart(Object.keys(d.bidStatusCounts), Object.values(d.bidStatusCounts));
        buildGeoChart(d.geoLabels, d.geoCounts);

        // Bid metric cards — update the 3 metric values after hero cards (indices 4,5,6 if they exist)
        // They use stat-number too, but let's target them precisely
        const metricNumbers = document.querySelectorAll('.border-l-4 .stat-number');
        if (metricNumbers[0]) { metricNumbers[0].setAttribute('data-target', d.avgBidValueK >= 1000 ? (d.avgBidValueK / 1000).toFixed(1) : d.avgBidValueK); animateNumber(metricNumbers[0]); }
        if (metricNumbers[1]) { metricNumbers[1].setAttribute('data-target', d.avgResponseHours); animateNumber(metricNumbers[1]); }
        if (metricNumbers[2]) { metricNumbers[2].setAttribute('data-target', d.bidWinRate); animateNumber(metricNumbers[2]); }

        // Progress bars
        const bars = document.querySelectorAll('.progress-bar');
        if (bars[0]) { bars[0].setAttribute('data-width', d.avgBidBarWidth); bars[0].style.width = d.avgBidBarWidth + '%'; }
        if (bars[1]) { bars[1].setAttribute('data-width', d.responseBarWidth); bars[1].style.width = d.responseBarWidth + '%'; }
        if (bars[2]) { bars[2].setAttribute('data-width', d.winRateBarWidth); bars[2].style.width = d.winRateBarWidth + '%'; }

        // Recent bids table
        updateRecentBidsTable(d.recentBids, d.avgBidValueK);

        // Owner activity table
        updateOwnerActivityTable(d.ownerActivity);

        // Payment analytics cards
        const paymentCards = document.querySelectorAll('#paymentAnalyticsCards .stat-number');
        if (paymentCards[0]) { paymentCards[0].setAttribute('data-target', d.paymentsReleasedM); animateNumber(paymentCards[0]); }
        if (paymentCards[1]) { paymentCards[1].setAttribute('data-target', d.pendingPaymentsM); animateNumber(paymentCards[1]); }
        if (paymentCards[2]) { paymentCards[2].setAttribute('data-target', d.avgPaymentDays); animateNumber(paymentCards[2]); }
        if (paymentCards[3]) { paymentCards[3].setAttribute('data-target', d.paymentSuccessRate); animateNumber(paymentCards[3]); }

        // Geographic district cards
        const districtCards = document.querySelectorAll('#districtCardsGrid .stat-number');
        const districtNames = ['Tetuan', 'Tumaga', 'Malagutay', 'Others'];
        districtCards.forEach((el, i) => {
          const idx = d.geoLabels ? d.geoLabels.indexOf(districtNames[i]) : -1;
          if (idx !== -1 && d.geoCounts) {
            el.setAttribute('data-target', d.geoCounts[idx]);
            animateNumber(el);
          }
        });

        if (loading) loading.classList.add('hidden');
      })
      .catch(err => { console.error('Bid data filter error:', err); if (loading) loading.classList.add('hidden'); });
  }

  function updateRecentBidsTable(bids, avgBidValueK) {
    const tbody = document.querySelector('.bg-gradient-to-r.from-violet-500 + .overflow-x-auto tbody');
    if (!tbody) return;
    if (!bids || bids.length === 0) {
      tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-12 text-center text-gray-400"><svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>No bid activity yet</td></tr>';
      return;
    }
    const gradients = ['from-blue-400 to-blue-600','from-purple-400 to-purple-600','from-emerald-400 to-emerald-600','from-orange-400 to-orange-600','from-red-400 to-red-600','from-indigo-400 to-indigo-600'];
    const statusCfg = {
      accepted:     { bg: 'bg-green-100',  text: 'text-green-800',  dot: 'bg-green-500',  pulse: true,  label: 'Accepted' },
      submitted:    { bg: 'bg-yellow-100', text: 'text-yellow-800', dot: 'bg-yellow-500', pulse: true,  label: 'Pending' },
      under_review: { bg: 'bg-blue-100',   text: 'text-blue-800',   dot: 'bg-blue-500',   pulse: false, label: 'Under Review' },
      rejected:     { bg: 'bg-red-100',    text: 'text-red-800',    dot: 'bg-red-500',    pulse: false, label: 'Rejected' },
      cancelled:    { bg: 'bg-gray-100',   text: 'text-gray-700',   dot: 'bg-gray-400',   pulse: false, label: 'Cancelled' },
    };
    tbody.innerHTML = bids.map((b, i) => {
      const sc = statusCfg[b.bid_status] || statusCfg.submitted;
      const grad = gradients[i % gradients.length];
      const avatar = b.company_logo
        ? '<img src="'+esc(b.company_logo)+'" class="w-10 h-10 rounded-full object-cover shadow" alt="'+esc(b.company_name)+'">'
        : '<div class="w-10 h-10 bg-gradient-to-br '+grad+' rounded-full flex items-center justify-center text-white font-semibold shadow text-sm">'+esc(b.initials)+'</div>';
      const diff = b.proposed_cost - (avgBidValueK * 1000);
      const sign = diff >= 0 ? '+' : '-';
      const diffK = Math.abs(diff / 1000).toFixed(1);
      return '<tr class="hover:bg-gray-50 transition-colors duration-150 cursor-pointer">' +
        '<td class="px-6 py-4"><div class="font-semibold text-gray-800">'+esc(b.project_title)+'</div><div class="text-sm text-gray-500">'+esc(b.project_location)+'</div></td>' +
        '<td class="px-6 py-4"><div class="flex items-center gap-3">'+avatar+'<div><div class="font-semibold text-gray-800">'+esc(b.company_name)+'</div></div></div></td>' +
        '<td class="px-6 py-4"><div class="font-bold text-gray-800">₱'+Number(b.proposed_cost).toLocaleString()+'</div><div class="text-sm text-gray-500">'+sign+'₱'+diffK+'K vs avg</div></td>' +
        '<td class="px-6 py-4"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold '+sc.bg+' '+sc.text+'"><span class="w-2 h-2 '+sc.dot+' rounded-full mr-2 '+(sc.pulse?'animate-pulse':'')+'"></span>'+sc.label+'</span></td>' +
        '<td class="px-6 py-4"><div class="text-sm text-gray-700">'+esc(b.submitted_at ? b.submitted_at.split(' ')[0] : '')+'</div><div class="text-xs text-gray-500">'+esc(b.submitted_ago)+'</div></td></tr>';
    }).join('');
  }

  function updateOwnerActivityTable(activities) {
    const tbody = document.querySelector('.bg-gradient-to-r.from-teal-500 tbody');
    if (!tbody) return;
    if (!activities || activities.length === 0) {
      tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-gray-400 text-sm">No recent activity</td></tr>';
      return;
    }
    const statusMap = { accepted: { cls: 'bg-green-100 text-green-800', label: 'Accepted' }, submitted: { cls: 'bg-yellow-100 text-yellow-800', label: 'Pending' }, under_review: { cls: 'bg-purple-100 text-purple-800', label: 'Under Review' }, rejected: { cls: 'bg-red-100 text-red-800', label: 'Rejected' }, cancelled: { cls: 'bg-gray-100 text-gray-700', label: 'Cancelled' } };
    tbody.innerHTML = activities.map(a => {
      const st = statusMap[a.bid_status] || statusMap.submitted;
      const cost = a.proposed_cost >= 1000000 ? '₱' + (a.proposed_cost / 1000000).toFixed(1) + 'M' : '₱' + Math.round(a.proposed_cost / 1000) + 'K';
      return '<tr class="hover:bg-gray-50 transition-colors duration-150">' +
        '<td class="px-4 py-4"><div class="font-semibold text-gray-800 text-sm">'+esc(a.project_title?.substring(0,30))+'</div><div class="text-xs text-gray-500">'+esc(a.project_location)+'</div></td>' +
        '<td class="px-4 py-4"><div class="font-medium text-gray-700 text-sm">'+esc(a.company_name)+'</div></td>' +
        '<td class="px-4 py-4"><div class="font-bold text-gray-800 text-sm">'+cost+'</div></td>' +
        '<td class="px-4 py-4"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold '+st.cls+'">'+st.label+'</span></td></tr>';
    }).join('');
  }

  // Preset buttons
  document.querySelectorAll('.date-preset-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.date-preset-btn').forEach(b => {
        b.style.color = '#4b5563'; b.style.background = '#fff'; b.style.borderColor = '#e5e7eb'; b.style.fontWeight = '500';
        b.classList.remove('active');
      });
      this.style.color = '#fff'; this.style.background = '#6366f1'; this.style.borderColor = '#6366f1'; this.style.fontWeight = '600';
      this.classList.add('active');
      const range = getDateRange(this.dataset.range);
      document.getElementById('globalDateFrom').value = range.from;
      document.getElementById('globalDateTo').value   = range.to;
      refreshBidData(range.from, range.to);
    });
  });

  // Custom date Apply button
  document.getElementById('applyGlobalDateFilter')?.addEventListener('click', function () {
    document.querySelectorAll('.date-preset-btn').forEach(b => {
      b.style.color = '#4b5563'; b.style.background = '#fff'; b.style.borderColor = '#e5e7eb'; b.style.fontWeight = '500';
      b.classList.remove('active');
    });
    refreshBidData(document.getElementById('globalDateFrom').value, document.getElementById('globalDateTo').value);
  });

});