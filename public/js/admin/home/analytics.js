document.addEventListener('DOMContentLoaded', function () {

  // ═══════════════════════════════════════════════════════════════════
  // CHART INSTANCES — kept global so we can destroy + recreate
  // ═══════════════════════════════════════════════════════════════════
  let projectsChart          = null;
  let successRateChart       = null;
  let projectsTimelineChart  = null;
  let completionTrendsChart  = null;
  let categoryChart          = null;

  const donutColors = ['#1f2937', '#60a5fa', '#34d399', '#a78bfa'];
  const donutHover  = ['#374151', '#3b82f6', '#10b981', '#8b5cf6'];
  const palette     = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

  const tooltipBase = {
    backgroundColor: 'rgba(255,255,255,0.95)',
    titleColor: '#1f2937', bodyColor: '#1f2937',
    borderColor: '#e5e7eb', borderWidth: 1, padding: 12, displayColors: true
  };

  function pctLabel(ctx) {
    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
    const pct   = ((ctx.parsed / total) * 100).toFixed(1);
    return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
  }

  // ═══════════════════════════════════════════════════════════════════
  // CHART BUILDERS
  // ═══════════════════════════════════════════════════════════════════
  function buildProjectsDonut(labels, values) {
    const el = document.getElementById('projectsDonutChart');
    if (!el) return;
    if (projectsChart) projectsChart.destroy();
    projectsChart = new Chart(el, {
      type: 'doughnut',
      data: { labels, datasets: [{ data: values, backgroundColor: donutColors, hoverBackgroundColor: donutHover, borderWidth: 0 }] },
      options: {
        responsive: true, maintainAspectRatio: true, cutout: '65%',
        plugins: { legend: { display: false }, tooltip: { ...tooltipBase, callbacks: { label: pctLabel } } },
        onHover: (e, a) => { e.native.target.style.cursor = a.length ? 'pointer' : 'default'; }
      }
    });
    initLegend('.legend-item', projectsChart);
  }

  function buildSuccessRate(labels, values, colors) {
    const el = document.getElementById('projectSuccessRateChart');
    if (!el) return;
    if (successRateChart) successRateChart.destroy();
    const hover = colors.map(c => {
      const r = parseInt(c.slice(1,3),16), g = parseInt(c.slice(3,5),16), b = parseInt(c.slice(5,7),16), f = 0.85;
      return '#' + Math.floor(r*f).toString(16).padStart(2,'0') + Math.floor(g*f).toString(16).padStart(2,'0') + Math.floor(b*f).toString(16).padStart(2,'0');
    });
    successRateChart = new Chart(el, {
      type: 'pie',
      data: { labels, datasets: [{ data: values, backgroundColor: colors, hoverBackgroundColor: hover, borderWidth: 0 }] },
      options: {
        responsive: true, maintainAspectRatio: true,
        plugins: { legend: { display: false }, tooltip: { ...tooltipBase, callbacks: { label: pctLabel } } },
        onHover: (e, a) => { e.native.target.style.cursor = a.length ? 'pointer' : 'default'; }
      }
    });
    initLegend('.success-rate-legend-item', successRateChart);
  }

  function buildTimeline(months, newP, compP) {
    const el = document.getElementById('projectsTimelineChart');
    if (!el) return;
    if (projectsTimelineChart) projectsTimelineChart.destroy();
    const dsCommon = { borderWidth: 3, fill: true, tension: 0.4, pointBorderColor: '#ffffff', pointBorderWidth: 2, pointRadius: 5, pointHoverRadius: 7, pointHoverBorderColor: '#ffffff', pointHoverBorderWidth: 3 };
    projectsTimelineChart = new Chart(el, {
      type: 'line',
      data: {
        labels: months,
        datasets: [
          { label: 'New Projects', data: newP, borderColor: '#fb923c', backgroundColor: 'rgba(251,146,60,0.1)', pointBackgroundColor: '#fb923c', pointHoverBackgroundColor: '#fb923c', ...dsCommon },
          { label: 'Completed Projects', data: compP, borderColor: '#818cf8', backgroundColor: 'rgba(129,140,248,0.1)', pointBackgroundColor: '#818cf8', pointHoverBackgroundColor: '#818cf8', ...dsCommon }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { display: false }, tooltip: { ...tooltipBase, boxWidth: 10, boxHeight: 10, usePointStyle: true, callbacks: { title: c => c[0].label, label: c => c.dataset.label + ': ' + c.parsed.y + ' Projects' } } },
        scales: {
          y: { beginAtZero: true, border: { display: false }, grid: { color: '#f3f4f6' }, ticks: { color: '#9ca3af', font: { size: 12 }, stepSize: 2 } },
          x: { border: { display: false }, grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 12 } } }
        }
      }
    });
  }

  function buildCompletionTrends(months, newD, compD) {
    const el = document.getElementById('completionTrendsChart');
    if (!el) return;
    if (completionTrendsChart) completionTrendsChart.destroy();
    completionTrendsChart = new Chart(el, {
      type: 'bar',
      data: {
        labels: months,
        datasets: [
          { label: 'New Projects', data: newD, backgroundColor: 'rgba(251,146,60,0.85)', borderRadius: 6, borderSkipped: false },
          { label: 'Completed', data: compD, backgroundColor: 'rgba(129,140,248,0.85)', borderRadius: 6, borderSkipped: false }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { position: 'top', labels: { font: { size: 12 }, usePointStyle: true, pointStyleWidth: 10 } },
          tooltip: { ...tooltipBase, callbacks: { label: c => c.dataset.label + ': ' + c.parsed.y } }
        },
        scales: {
          x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#9ca3af' } },
          y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0, font: { size: 11 }, color: '#9ca3af' } }
        }
      }
    });
  }

  function buildCategoryChart(labels, values) {
    const el = document.getElementById('categoryPerformanceChart');
    if (!el) return;
    if (categoryChart) categoryChart.destroy();
    categoryChart = new Chart(el, {
      type: 'doughnut',
      data: { labels, datasets: [{ data: values, backgroundColor: palette.slice(0, labels.length), borderWidth: 2, borderColor: '#fff', hoverBorderWidth: 3 }] },
      options: {
        responsive: true, maintainAspectRatio: false, cutout: '65%',
        plugins: {
          legend: { position: 'right', labels: { font: { size: 12 }, usePointStyle: true, padding: 16 } },
          tooltip: { ...tooltipBase, callbacks: { label: pctLabel } }
        },
        onHover: (e, a) => { e.native.target.style.cursor = a.length ? 'pointer' : 'default'; }
      }
    });
  }

  function initLegend(selector, chart) {
    document.querySelectorAll(selector).forEach((item, index) => {
      item.addEventListener('click', function () {
        const meta = chart.getDatasetMeta(0);
        if (meta.data[index]) { meta.data[index].hidden = !meta.data[index].hidden; chart.update(); this.classList.toggle('active'); }
      });
      item.addEventListener('mouseenter', function () { chart.setActiveElements([{ datasetIndex: 0, index }]); chart.update(); });
      item.addEventListener('mouseleave', function () { chart.setActiveElements([]); chart.update(); });
    });
  }


  // ═══════════════════════════════════════════════════════════════════
  // INITIAL RENDER — read data-* attributes from server-rendered HTML
  // ═══════════════════════════════════════════════════════════════════
  function initFromDataAttributes() {
    const pEl = document.getElementById('projectsDonutChart');
    if (pEl) buildProjectsDonut(JSON.parse(pEl.dataset.labels || '[]'), JSON.parse(pEl.dataset.values || '[]'));

    const sEl = document.getElementById('projectSuccessRateChart');
    if (sEl) buildSuccessRate(JSON.parse(sEl.dataset.labels || '[]'), JSON.parse(sEl.dataset.values || '[]'), JSON.parse(sEl.dataset.colors || '[]'));

    const tEl = document.getElementById('projectsTimelineChart');
    if (tEl) buildTimeline(JSON.parse(tEl.dataset.months || '[]'), JSON.parse(tEl.dataset.new || '[]'), JSON.parse(tEl.dataset.completed || '[]'));

    const trEl = document.getElementById('completionTrendsChart');
    if (trEl) buildCompletionTrends(JSON.parse(trEl.dataset.months || '[]'), JSON.parse(trEl.dataset.new || '[]'), JSON.parse(trEl.dataset.completed || '[]'));

    const cEl = document.getElementById('categoryPerformanceChart');
    if (cEl) buildCategoryChart(JSON.parse(cEl.dataset.labels || '[]'), JSON.parse(cEl.dataset.values || '[]'));
  }

  initFromDataAttributes();


  // ═══════════════════════════════════════════════════════════════════
  // TIMELINE DROPDOWN (local range switch, overridden by global filter)
  // ═══════════════════════════════════════════════════════════════════
  const dropdownBtn  = document.getElementById('timelineDropdownBtn');
  const dropdownMenu = document.getElementById('timelineDropdownMenu');

  if (dropdownBtn && dropdownMenu) {
    dropdownBtn.addEventListener('click', e => { e.stopPropagation(); dropdownMenu.classList.toggle('active'); dropdownBtn.classList.toggle('active'); });
    document.addEventListener('click', e => { if (!dropdownMenu.contains(e.target) && !dropdownBtn.contains(e.target)) { dropdownMenu.classList.remove('active'); dropdownBtn.classList.remove('active'); } });
    dropdownMenu.querySelectorAll('.timeline-dropdown-item').forEach(item => {
      item.addEventListener('click', function () {
        dropdownMenu.querySelectorAll('.timeline-dropdown-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
        dropdownMenu.classList.remove('active');
        dropdownBtn.classList.remove('active');
        fetchTimelineData(this.dataset.range);
      });
    });
  }

  document.querySelectorAll('.timeline-legend-item').forEach(item => {
    item.addEventListener('click', function () {
      const idx = parseInt(this.dataset.dataset);
      if (projectsTimelineChart) { const meta = projectsTimelineChart.getDatasetMeta(idx); meta.hidden = !meta.hidden; projectsTimelineChart.update(); this.classList.toggle('hidden'); }
    });
  });

  function fetchTimelineData(range) {
    const container = document.querySelector('.timeline-chart-container');
    if (container) container.style.opacity = '0.5';
    fetch('/admin/analytics/timeline?range=' + encodeURIComponent(range))
      .then(r => r.json())
      .then(data => {
        if (projectsTimelineChart) { projectsTimelineChart.data.labels = data.months; projectsTimelineChart.data.datasets[0].data = data.newProjects; projectsTimelineChart.data.datasets[1].data = data.completedProjects; projectsTimelineChart.update(); }
        const rangeEl = document.querySelector('.timeline-date-range');
        if (rangeEl) rangeEl.textContent = data.dateRange;
        if (container) container.style.opacity = '1';
      })
      .catch(() => { if (container) container.style.opacity = '1'; });
  }


  // ═══════════════════════════════════════════════════════════════════
  // PERFORMANCE KPI: ANIMATED COUNTERS + PROGRESS BARS
  // ═══════════════════════════════════════════════════════════════════
  function animateCounters() {
    document.querySelectorAll('.perf-counter').forEach(el => {
      const target = parseFloat(el.dataset.target) || 0;
      const isFloat = target % 1 !== 0;
      const duration = 1400, stepMs = 16;
      const steps = duration / stepMs, increment = target / steps;
      let current = 0;
      const timer = setInterval(() => {
        current += increment;
        if (current >= target) { current = target; clearInterval(timer); }
        el.textContent = isFloat ? current.toFixed(1) : Math.floor(current).toLocaleString();
      }, stepMs);
    });
  }

  function animateProgressBars() {
    document.querySelectorAll('.progress-bar-fill').forEach(el => {
      setTimeout(() => { el.style.width = (el.dataset.width || 0) + '%'; }, 200);
    });
  }

  const firstPerfCard = document.querySelector('.perf-card');
  if (firstPerfCard) {
    const obs = new IntersectionObserver(entries => {
      entries.forEach(entry => { if (entry.isIntersecting) { animateCounters(); animateProgressBars(); obs.disconnect(); } });
    }, { threshold: 0.1 });
    obs.observe(firstPerfCard);
  }


  // ═══════════════════════════════════════════════════════════════════
  // GLOBAL DATE FILTER — AJAX REFRESH
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

  function showFilterLoading(show) {
    const el = document.getElementById('filterLoading');
    if (el) el.classList.toggle('hidden', !show);
  }

  function refreshAllData(dateFrom, dateTo) {
    showFilterLoading(true);
    const params = new URLSearchParams();
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo)   params.set('date_to', dateTo);

    fetch('/admin/analytics/project-data?' + params.toString())
      .then(r => r.json())
      .then(data => {
        // -- Overview charts
        const pa = data.projectsAnalytics;
        buildProjectsDonut(pa.data.map(d => d.label), pa.data.map(d => d.count));
        updateDonutLegend(pa.data);

        const sr = data.projectSuccessRate;
        buildSuccessRate(sr.data.map(d => d.label), sr.data.map(d => d.count), sr.data.map(d => d.color));
        updateSuccessRateLegend(sr.data);

        const tl = data.projectsTimeline;
        buildTimeline(tl.months, tl.newProjects, tl.completedProjects);
        const rangeEl = document.querySelector('.timeline-date-range');
        if (rangeEl) rangeEl.textContent = tl.dateRange;

        // -- Performance KPI cards
        const pp = data.projectPerformance;
        const bm = data.bidMetrics;
        updateKpiCards(pp, bm);

        // -- Completion trends + category chart
        buildCompletionTrends(
          pp.completion_trends.map(t => t.month),
          pp.completion_trends.map(t => t.new),
          pp.completion_trends.map(t => t.completed)
        );
        buildCategoryChart(Object.keys(pp.by_property_type), Object.values(pp.by_property_type));

        // -- Bid pills
        updateBidPills(bm);

        // -- Top Contractors
        updateContractorsTable(data.topContractors);

        showFilterLoading(false);
      })
      .catch(err => { console.error('Filter fetch error:', err); showFilterLoading(false); });
  }

  // DOM updaters for non-chart elements
  function updateDonutLegend(data) {
    const items = document.querySelectorAll('.legend-item');
    items.forEach((item, i) => {
      const countEl = item.querySelector('.legend-count');
      const labelEl = item.querySelector('.legend-label');
      if (data[i]) {
        if (countEl) countEl.textContent = data[i].count;
        if (labelEl) labelEl.textContent = data[i].label;
        item.style.display = '';
      } else {
        item.style.display = 'none';
      }
    });
  }

  function updateSuccessRateLegend(data) {
    const items = document.querySelectorAll('.success-rate-legend-item');
    items.forEach((item, i) => {
      if (data[i]) {
        const labelEl = item.querySelector('.success-rate-legend-label');
        const pctEl   = item.querySelector('span[style]');
        if (labelEl) labelEl.textContent = data[i].label;
        if (pctEl) { pctEl.textContent = data[i].percentage + '%'; pctEl.style.background = data[i].color + '20'; pctEl.style.color = data[i].color; }
        item.style.display = '';
      } else {
        item.style.display = 'none';
      }
    });
  }

  function updateKpiCards(pp, bm) {
    const counters = document.querySelectorAll('.perf-counter');
    const targets  = [pp.total_projects, pp.completed_projects, pp.total_bids, pp.avg_duration, pp.completion_rate, pp.on_time_rate];
    counters.forEach((el, i) => {
      if (targets[i] !== undefined) { el.dataset.target = targets[i]; el.textContent = targets[i]; }
    });

    // Update contracted value text
    const valueEl = document.querySelector('.perf-card:nth-child(4) .text-3xl');
    if (valueEl && pp.total_value !== undefined) {
      valueEl.textContent = '₱' + (pp.total_value / 1000000).toFixed(1) + 'M';
    }

    // Update hero card progress bars
    const heroFills = document.querySelectorAll('.perf-card .progress-bar-fill');
    const heroWidths = [
      Math.min(100, pp.total_projects),
      pp.completion_rate,
      bm ? bm.acceptance_rate : null,
      Math.min(100, Math.round(pp.total_value / 10000000 * 100))
    ];
    heroFills.forEach((bar, i) => {
      if (heroWidths[i] !== null && heroWidths[i] !== undefined) {
        bar.dataset.width = heroWidths[i];
        bar.style.width = heroWidths[i] + '%';
      }
    });

    // Update metric-sub progress bars
    const metricFills = document.querySelectorAll('.metric-sub .progress-bar-fill');
    const metricWidths = [
      Math.min(100, Math.round(pp.avg_duration)),
      pp.completion_rate,
      pp.on_time_rate
    ];
    metricFills.forEach((bar, i) => {
      if (metricWidths[i] !== undefined) {
        bar.dataset.width = metricWidths[i];
        bar.style.width = metricWidths[i] + '%';
      }
    });
  }

  function updateBidPills(bm) {
    const bidValues = [bm.total, bm.accepted, bm.rejected, bm.pending, bm.cancelled, bm.acceptance_rate + '%'];
    const pills = document.querySelectorAll('.grid.grid-cols-2.sm\\:grid-cols-3.lg\\:grid-cols-6 > div');
    pills.forEach((pill, i) => {
      const valEl = pill.querySelector('p:first-child');
      if (valEl && bidValues[i] !== undefined) valEl.textContent = bidValues[i];
    });
  }

  function updateContractorsTable(contractors) {
    const wrap = document.getElementById('contractorsTableWrap');
    if (!wrap) return;

    if (!contractors || contractors.length === 0) {
      wrap.innerHTML = '<div class="py-16 text-center"><svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><p class="text-gray-400 font-medium">No contractor data found</p></div>';
      return;
    }

    const avatarColors = ['from-blue-400 to-blue-600','from-purple-400 to-purple-600','from-emerald-400 to-emerald-600','from-orange-400 to-orange-600','from-red-400 to-red-600'];
    const rankColors = {
      1: 'bg-gradient-to-br from-yellow-400 to-yellow-500 text-white',
      2: 'bg-gradient-to-br from-gray-300 to-gray-400 text-white',
      3: 'bg-gradient-to-br from-orange-300 to-orange-400 text-white'
    };

    let html = '<div class="overflow-x-auto"><table class="w-full contractors-table"><thead><tr><th class="text-left">Rank</th><th class="text-left">Contractor</th><th class="text-left">Completed</th><th class="text-left">Success Rate</th><th class="text-left">Experience</th><th class="text-left">Avg Rating</th></tr></thead><tbody>';

    contractors.forEach(c => {
      const rc = rankColors[c.rank] || 'bg-gray-100 text-gray-600';
      const ac = avatarColors[(c.rank - 1) % avatarColors.length];
      const avatar = c.company_logo
        ? '<img src="' + c.company_logo + '" alt="" class="w-10 h-10 rounded-full object-cover shadow">'
        : '<div class="w-10 h-10 bg-gradient-to-br ' + ac + ' rounded-full flex items-center justify-center text-white font-bold text-sm shadow">' + c.initials + '</div>';
      const stars = c.avg_rating > 0
        ? '<div class="flex items-center gap-1"><svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg><span class="text-sm font-bold text-gray-700">' + c.avg_rating + '</span><span class="text-xs text-gray-400">(' + c.review_count + ')</span></div>'
        : '<span class="text-xs text-gray-400 italic">No reviews</span>';

      html += '<tr class="cursor-pointer"><td><div class="rank-badge ' + rc + '">' + c.rank + '</div></td>' +
        '<td><div class="flex items-center gap-3">' + avatar + '<div><p class="font-semibold text-gray-800">' + c.company_name + '</p><p class="text-xs text-gray-500">' + c.rep_name + '</p></div></div></td>' +
        '<td><span class="bid-pill bg-blue-100 text-blue-700"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>' + c.completed_projects + ' done</span></td>' +
        '<td><div class="flex items-center gap-2 min-w-[120px]"><div class="flex-1 bg-gray-100 rounded-full h-1.5 w-20"><div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-1.5 rounded-full" style="width:' + c.success_rate + '%"></div></div><span class="text-sm font-bold text-gray-700 w-10 text-right">' + c.success_rate + '%</span></div></td>' +
        '<td class="text-gray-600 font-medium">' + c.years_of_experience + ' yrs</td>' +
        '<td>' + stars + '</td></tr>';
    });

    html += '</tbody></table></div>';
    wrap.innerHTML = html;
  }


  // ═══════════════════════════════════════════════════════════════════
  // DATE FILTER EVENT LISTENERS
  // ═══════════════════════════════════════════════════════════════════
  document.querySelectorAll('.date-preset-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.date-preset-btn').forEach(b => {
        b.classList.remove('active', 'border-indigo-500', 'text-white', 'bg-indigo-500', 'font-semibold');
        b.classList.add('border-gray-200', 'text-gray-600', 'font-medium');
      });
      this.classList.add('active', 'border-indigo-500', 'text-white', 'bg-indigo-500', 'font-semibold');
      this.classList.remove('border-gray-200', 'text-gray-600', 'font-medium');

      const range = getDateRange(this.dataset.range);
      document.getElementById('dateFrom').value = range.from;
      document.getElementById('dateTo').value   = range.to;
      refreshAllData(range.from, range.to);
    });
  });

  const applyBtn = document.getElementById('applyDateFilter');
  if (applyBtn) {
    applyBtn.addEventListener('click', function () {
      document.querySelectorAll('.date-preset-btn').forEach(b => {
        b.classList.remove('active', 'border-indigo-500', 'text-white', 'bg-indigo-500', 'font-semibold');
        b.classList.add('border-gray-200', 'text-gray-600', 'font-medium');
      });
      refreshAllData(document.getElementById('dateFrom').value, document.getElementById('dateTo').value);
    });
  }


  // ═══════════════════════════════════════════════════════════════════
  // TOP CONTRACTORS — OWN SEARCH + DATE FILTER
  // ═══════════════════════════════════════════════════════════════════
  const contractorFilterBtn = document.getElementById('contractorFilterBtn');
  if (contractorFilterBtn) {
    contractorFilterBtn.addEventListener('click', function () {
      const search   = document.getElementById('contractorSearch').value;
      const dateFrom = document.getElementById('contractorDateFrom').value;
      const dateTo   = document.getElementById('contractorDateTo').value;

      const params = new URLSearchParams();
      if (search)   params.set('search', search);
      if (dateFrom) params.set('date_from', dateFrom);
      if (dateTo)   params.set('date_to', dateTo);

      fetch('/admin/analytics/top-contractors-data?' + params.toString())
        .then(r => r.json())
        .then(data => { updateContractorsTable(data.topContractors); })
        .catch(err => console.error('Contractor filter error:', err));
    });
  }

});