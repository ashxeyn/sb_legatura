/**
 * subscriptionAnalytics.js
 *
 * BUG 4 FIX: All filter/search/pagination changes now use AJAX + history.pushState.
 *            The subscriber panel is re-rendered in-place — the page never scrolls to top.
 *
 * BUG 2 FIX: Revenue chart reads keys 'currentYearData'/'previousYearData' to match
 *            what the controller now returns.
 */

'use strict';

// ── Utilities ──────────────────────────────────────────────────────────────────

/** Eased counter animation */
function animateCounter(el) {
  const target   = parseFloat(el.dataset.target) || 0;
  const isFloat  = !Number.isInteger(target);
  const dur      = 1200;
  const t0       = performance.now();

  const tick = (now) => {
    const p = Math.min((now - t0) / dur, 1);
    const e = 1 - Math.pow(1 - p, 3); // easeOutCubic
    const v = target * e;
    el.textContent = isFloat ? v.toFixed(1) : Math.floor(v).toLocaleString();
    if (p < 1) requestAnimationFrame(tick);
  };
  requestAnimationFrame(tick);
}

/** Animate tier progress bars on scroll into view */
function initTierBars() {
  const bars = document.querySelectorAll('.tier-bar');
  if (!bars.length) return;

  const obs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const bar = entry.target;
      setTimeout(() => { bar.style.width = bar.dataset.width + '%'; }, 100);
      obs.unobserve(bar);
    });
  }, { threshold: 0.4 });

  bars.forEach(b => obs.observe(b));
}

// ── Revenue Chart ───────────────────────────────────────────────────────────────

let revenueChart = null;

function buildRevenueChart(months, currentData, previousData, currentYear, previousYear) {
  const canvas = document.getElementById('revenueChart');
  if (!canvas) return;

  const ctx = canvas.getContext('2d');

  // gradient fill for current year line
  const grad = ctx.createLinearGradient(0, 0, 0, 300);
  grad.addColorStop(0, 'rgba(99,102,241,0.3)');
  grad.addColorStop(1, 'rgba(99,102,241,0)');

  if (revenueChart) revenueChart.destroy();

  revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: months,
      datasets: [
        {
          label: String(currentYear),
          data: currentData,
          borderColor: 'rgb(99,102,241)',
          backgroundColor: grad,
          fill: true,
          tension: 0.4,
          borderWidth: 3,
          pointRadius: 5,
          pointHoverRadius: 7,
          pointBackgroundColor: 'rgb(99,102,241)',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
        },
        {
          label: String(previousYear),
          data: previousData,
          borderColor: 'rgb(203,213,225)',
          backgroundColor: 'transparent',
          fill: false,
          tension: 0.4,
          borderWidth: 2,
          borderDash: [6, 3],
          pointRadius: 3,
          pointHoverRadius: 5,
          pointBackgroundColor: 'rgb(203,213,225)',
          pointBorderColor: '#fff',
          pointBorderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: {
          position: 'bottom',
          labels: { usePointStyle: true, padding: 20, font: { size: 13, weight: '600' } },
        },
        tooltip: {
          backgroundColor: '#fff',
          titleColor: '#1f2937',
          bodyColor: '#374151',
          borderColor: '#e5e7eb',
          borderWidth: 1,
          padding: 12,
          cornerRadius: 10,
          callbacks: {
            label: ctx => `  ${ctx.dataset.label}: ₱${ctx.parsed.y.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`,
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
          ticks: {
            padding: 8,
            font: { size: 11 },
            callback: v => '₱' + (v >= 1000 ? (v / 1000).toFixed(0) + 'K' : v),
          },
        },
        x: {
          grid: { display: false, drawBorder: false },
          ticks: { padding: 6, font: { size: 11 } },
        },
      },
    },
  });
}

function initRevenueChart() {
  const canvas = document.getElementById('revenueChart');
  if (!canvas) return;

  buildRevenueChart(
    JSON.parse(canvas.dataset.months   || '[]'),
    JSON.parse(canvas.dataset.current  || '[]'),   // BUG 2 FIX: read data-current (set from currentYearData)
    JSON.parse(canvas.dataset.previous || '[]'),   // data-previous (set from previousYearData)
    canvas.dataset.currentYear,
    canvas.dataset.previousYear
  );
}

function initTierButtons() {
  const btns    = document.querySelectorAll('.tier-btn');
  const spinner = document.getElementById('revenueSpinner');
  const subtitle = document.getElementById('revenueSubtitle');

  btns.forEach(btn => {
    btn.addEventListener('click', async function () {
      // Visual state
      btns.forEach(b => {
        b.classList.remove('active', 'bg-gray-800', 'text-white', 'ring-2', 'ring-offset-1');
        b.style.opacity = '0.6';
      });
      this.classList.add('active');
      this.style.opacity = '1';
      if (this.dataset.tier === 'all') {
        this.classList.add('bg-gray-800', 'text-white');
      }

      // Fetch
      if (spinner) spinner.classList.remove('hidden');
      try {
        const url = `${window.SubConfig.revenueUrl}?tier=${this.dataset.tier}`;
        const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) throw new Error('Network error');
        const data = await res.json();

        buildRevenueChart(
          data.months,
          data.currentYearData,   // BUG 2 FIX: controller now returns this key
          data.previousYearData,
          data.currentYear,
          data.previousYear
        );

        if (subtitle) {
          subtitle.textContent = `${data.dateRange} · Current vs Previous Year`;
        }
      } catch (err) {
        console.error('Revenue fetch error:', err);
      } finally {
        if (spinner) spinner.classList.add('hidden');
      }
    });
  });

  // Restore opacity for non-active buttons
  btns.forEach(b => {
    if (!b.classList.contains('active')) b.style.opacity = '0.65';
  });
}

// ── Subscriber Table AJAX ───────────────────────────────────────────────────────
// BUG 4 FIX: all filter/search/page changes fetch JSON and re-render the panel
//            in place, without ever reloading the page or scrolling to top.

let currentPage    = 1;
let isLoadingSubs  = false;

/** Collect current filter values from the DOM controls */
function getFilterParams(page = 1) {
  return {
    search: document.getElementById('searchInput')?.value.trim()   ?? '',
    plan:   document.getElementById('planFilter')?.value           ?? '',
    status: document.getElementById('statusFilter')?.value         ?? '',
    sort:   document.getElementById('sortFilter')?.value           ?? 'newest',
    page,
  };
}

/** Build a query string from a plain object */
function toQS(params) {
  return Object.entries(params)
    .filter(([, v]) => v !== '' && v !== null && v !== undefined)
    .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`)
    .join('&');
}

/** Show skeleton rows while loading */
function showSkeleton() {
  const panel = document.getElementById('subscriberPanel');
  if (!panel) return;
  panel.innerHTML = `
    <div class="overflow-x-auto">
      <table class="w-full sa-sub-table">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50">
            ${['Subscriber','Plan','Amount','Status','Subscribed','Expires','TXN #']
                .map(h => `<th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">${h}</th>`)
                .join('')}
          </tr>
        </thead>
        <tbody>
          ${Array.from({ length: 10 }, (_, i) => `
            <tr class="border-b border-gray-50" style="animation-delay:${i * 40}ms">
              <td class="px-3 py-2"><div class="flex items-center gap-2">
                <div class="skeleton w-7 h-7 rounded-full shrink-0"></div>
                <div class="space-y-1.5 flex-1">
                  <div class="skeleton h-2.5 w-28 rounded"></div>
                  <div class="skeleton h-2 w-16 rounded"></div>
                </div>
              </div></td>
              <td class="px-3 py-2"><div class="skeleton h-4 w-14 rounded-full"></div></td>
              <td class="px-3 py-2"><div class="skeleton h-2.5 w-16 rounded"></div></td>
              <td class="px-3 py-2"><div class="skeleton h-4 w-14 rounded-full"></div></td>
              <td class="px-3 py-2"><div class="skeleton h-2.5 w-20 rounded"></div></td>
              <td class="px-3 py-2"><div class="skeleton h-2.5 w-20 rounded"></div></td>
              <td class="px-3 py-2"><div class="skeleton h-2.5 w-24 rounded"></div></td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>`;
}

/** Render JSON subscriber data into the panel as an HTML table */
function renderSubscribers(data) {
  const panel = document.getElementById('subscriberPanel');
  if (!panel) return;

  // Update the header meta line
  const meta = document.getElementById('subscriberMeta');
  if (meta) {
    meta.textContent = `${data.total} total · Showing ${data.from}–${data.to}`;
  }

  if (!data.data || data.data.length === 0) {
    panel.innerHTML = `
      <div class="py-16 text-center">
        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <p class="text-sm text-gray-400 font-medium">No subscribers match your filters</p>
        <p class="text-xs text-gray-300 mt-1">Try adjusting the search or clearing filters</p>
      </div>`;
    return;
  }

  const tierBadge = { gold: 'tier-gold', silver: 'tier-silver', bronze: 'tier-bronze', boost: 'tier-boost' };
  const statusCfg = {
    active:    { cls: 'status-active',    dot: 'bg-emerald-500', label: 'Active' },
    expired:   { cls: 'status-expired',   dot: 'bg-red-500',     label: 'Expired' },
    pending:   { cls: 'status-pending',   dot: 'bg-yellow-500',  label: 'Pending' },
    cancelled: { cls: 'status-cancelled', dot: 'bg-gray-400',    label: 'Cancelled' },
  };
  const avatarColors = [
    { bg: 'bg-indigo-100',  text: 'text-indigo-700' },
    { bg: 'bg-violet-100',  text: 'text-violet-700' },
    { bg: 'bg-emerald-100', text: 'text-emerald-700' },
    { bg: 'bg-amber-100',   text: 'text-amber-700' },
    { bg: 'bg-rose-100',    text: 'text-rose-700' },
    { bg: 'bg-cyan-100',    text: 'text-cyan-700' },
    { bg: 'bg-fuchsia-100', text: 'text-fuchsia-700' },
  ];

  const escape = s => s ? String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;') : '';

  const rows = data.data.map((s, i) => {
    const ac      = avatarColors[i % avatarColors.length];
    const badge   = tierBadge[s.plan_key]              ?? 'tier-other';
    const st      = statusCfg[s.subscription_status]   ?? statusCfg.cancelled;
    const typeClr = s.subscriber_type === 'Contractor' ? 'bg-indigo-50 text-indigo-600' : 'bg-teal-50 text-teal-700';
    const avatar  = s.avatar
      ? `<img src="${escape(s.avatar)}" alt="${escape(s.subscriber_name)}" class="w-7 h-7 rounded-full object-cover ring-1 ring-gray-100 shrink-0">`
      : `<div class="w-7 h-7 rounded-full ${ac.bg} ${ac.text} flex items-center justify-center text-[10px] font-bold shrink-0">${escape(s.initials)}</div>`;

    const emailLine = s.subscriber_email
      ? `<div class="text-[10px] text-gray-400 truncate max-w-[160px]">${escape(s.subscriber_email)}</div>` : '';

    const expiringSoonBadge = s.expiring_soon
      ? `<div class="text-[10px] text-amber-600 font-medium mt-0.5">⚠ Expiring soon</div>` : '';

    const expiryCell = s.expiration_fmt
      ? `<div class="text-[11px] font-medium ${s.expiration_past ? 'text-red-500' : 'text-gray-800'}">${escape(s.expiration_fmt)}</div>
         <div class="text-[10px] ${s.expiration_past ? 'text-red-400' : 'text-gray-400'}">${escape(s.expiration_rel)}</div>`
      : `<span class="text-[10px] text-gray-300 italic">No expiry</span>`;

    return `
      <tr class="sub-row border-b border-gray-50" style="animation: fadeIn .25s ease both; animation-delay:${i * 20}ms">
        <td class="px-3 py-2">
          <div class="flex items-center gap-2">
            ${avatar}
            <div class="min-w-0">
              <div class="font-semibold text-gray-800 leading-tight truncate max-w-[160px] text-xs">${escape(s.subscriber_name)}</div>
              ${emailLine}
              <span class="inline-flex items-center px-1.5 py-px rounded text-[10px] font-semibold ${typeClr}">${escape(s.subscriber_type)}</span>
            </div>
          </div>
        </td>
        <td class="px-3 py-2">
          <span class="inline-flex items-center px-1.5 py-px rounded-full text-[10px] font-semibold ${badge}">${escape(s.plan_key.charAt(0).toUpperCase() + s.plan_key.slice(1))}</span>
          <div class="text-[10px] text-gray-400 capitalize mt-0.5">${escape(s.billing_cycle)}</div>
        </td>
        <td class="px-3 py-2">
          <div class="font-bold text-gray-800 text-xs">${escape(s.amount_fmt)}</div>
          <div class="text-[10px] text-gray-400">${escape(s.payment_type ?? '')}</div>
        </td>
        <td class="px-3 py-2">
          <span class="inline-flex items-center gap-1 px-1.5 py-px rounded-full text-[10px] font-semibold ${st.cls}">
            <span class="w-1.5 h-1.5 rounded-full ${st.dot} inline-block shrink-0"></span>${st.label}
          </span>
          ${expiringSoonBadge}
        </td>
        <td class="px-3 py-2">
          <div class="text-[11px] text-gray-800 font-medium">${escape(s.transaction_date_fmt)}</div>
          <div class="text-[10px] text-gray-400">${escape(s.transaction_date_rel)}</div>
        </td>
        <td class="px-3 py-2">${expiryCell}</td>
        <td class="px-3 py-2">
          <div class="text-[10px] font-mono text-gray-400 max-w-[120px] truncate" title="${escape(s.transaction_number ?? '')}">${escape(s.transaction_number ?? '—')}</div>
        </td>
      </tr>`;
  }).join('');

  // Pagination
  let pagHtml = '';
  if (data.last_page > 1) {
    const cur   = data.current_page;
    const last  = data.last_page;
    const start = Math.max(1, cur - 2);
    const end   = Math.min(last, cur + 2);

    const prevBtn = cur === 1
      ? `<span class="px-2.5 py-1.5 rounded-lg text-xs text-gray-300 select-none">← Prev</span>`
      : `<button class="page-btn px-2.5 py-1.5 rounded-lg text-xs text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" data-page="${cur - 1}">← Prev</button>`;

    let pages = '';
    if (start > 1) {
      pages += `<button class="page-btn px-2.5 py-1.5 rounded-lg text-xs text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" data-page="1">1</button>`;
      if (start > 2) pages += `<span class="px-1.5 text-gray-300 text-xs">…</span>`;
    }
    for (let p = start; p <= end; p++) {
      pages += p === cur
        ? `<span class="px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white">${p}</span>`
        : `<button class="page-btn px-2.5 py-1.5 rounded-lg text-xs text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" data-page="${p}">${p}</button>`;
    }
    if (end < last) {
      if (end < last - 1) pages += `<span class="px-1.5 text-gray-300 text-xs">…</span>`;
      pages += `<button class="page-btn px-2.5 py-1.5 rounded-lg text-xs text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" data-page="${last}">${last}</button>`;
    }

    const nextBtn = cur < last
      ? `<button class="page-btn px-2.5 py-1.5 rounded-lg text-xs text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" data-page="${cur + 1}">Next →</button>`
      : `<span class="px-2.5 py-1.5 rounded-lg text-xs text-gray-300 select-none">Next →</span>`;

    pagHtml = `
      <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100" id="paginationRow">
        <div class="text-xs text-gray-500">
          Showing <span class="font-semibold text-gray-700">${data.from}</span>
          &ndash; <span class="font-semibold text-gray-700">${data.to}</span>
          of <span class="font-semibold text-gray-700">${data.total}</span>
        </div>
        <div class="flex items-center gap-1 flex-wrap">
          ${prevBtn}${pages}${nextBtn}
        </div>
      </div>`;
  }

  panel.innerHTML = `
    <style>@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}</style>
    <div class="overflow-x-auto">
      <table class="w-full text-sm sa-sub-table">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50">
            ${['Subscriber','Plan','Amount','Status','Subscribed','Expires','TXN #']
                .map(h => `<th class="px-3 py-2 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">${h}</th>`)
                .join('')}
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">${rows}</tbody>
      </table>
    </div>
    ${pagHtml}`;

  // Re-attach pagination click handlers
  attachPageBtnListeners();
}

/** Fetch subscribers from JSON endpoint, update panel + URL */
async function fetchSubscribers(params) {
  if (isLoadingSubs) return;
  isLoadingSubs = true;

  showSkeleton();

  const qs  = toQS(params);
  const url = `${window.SubConfig.ajaxUrl}?${qs}`;

  // BUG 4 FIX: update browser URL without page reload
  const browserUrl = `${window.location.pathname}?${qs}`;
  history.pushState({ params }, '', browserUrl);

  try {
    const res  = await fetch(url, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': window.SubConfig.csrfToken,
      },
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    renderSubscribers(data);
    currentPage = data.current_page;
  } catch (err) {
    console.error('Subscriber fetch error:', err);
    const panel = document.getElementById('subscriberPanel');
    if (panel) {
      panel.innerHTML = `<div class="py-16 text-center text-red-400 text-sm">
        Failed to load subscribers. <button onclick="fetchSubscribers(getFilterParams(${currentPage}))" class="underline text-indigo-500 ml-1">Retry</button>
      </div>`;
    }
  } finally {
    isLoadingSubs = false;
  }
}

/** Wire up page buttons rendered inside the panel */
function attachPageBtnListeners() {
  document.querySelectorAll('.page-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const p = parseInt(this.dataset.page, 10);
      if (!p) return;
      fetchSubscribers(getFilterParams(p));
    });
  });
}

/** Wire up filter controls — debounce search, instant for dropdowns */
function initFilterControls() {
  let searchTimer;

  document.getElementById('searchInput')?.addEventListener('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => fetchSubscribers(getFilterParams(1)), 450);
  });

  ['planFilter', 'statusFilter', 'sortFilter'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', () => {
      fetchSubscribers(getFilterParams(1));
    });
  });

  document.getElementById('clearFiltersBtn')?.addEventListener('click', () => {
    const search = document.getElementById('searchInput');
    const plan   = document.getElementById('planFilter');
    const status = document.getElementById('statusFilter');
    const sort   = document.getElementById('sortFilter');
    if (search) search.value = '';
    if (plan)   plan.value   = '';
    if (status) status.value = '';
    if (sort)   sort.value   = 'newest';
    fetchSubscribers({ search: '', plan: '', status: '', sort: 'newest', page: 1 });
  });

  // Handle browser back/forward
  window.addEventListener('popstate', (e) => {
    if (e.state?.params) {
      const p = e.state.params;
      const search = document.getElementById('searchInput');
      const plan   = document.getElementById('planFilter');
      const status = document.getElementById('statusFilter');
      const sort   = document.getElementById('sortFilter');
      if (search) search.value = p.search ?? '';
      if (plan)   plan.value   = p.plan   ?? '';
      if (status) status.value = p.status ?? '';
      if (sort)   sort.value   = p.sort   ?? 'newest';
      fetchSubscribers(p);
    }
  });
}

// ── CSV Export (from current visible AJAX data) ─────────────────────────────────

let lastJsonData = null; // stores last AJAX response for export

const _origRender = renderSubscribers;
window.renderSubscribers = function (data) {
  lastJsonData = data;
  _origRender(data);
};

function initExport() {
  document.getElementById('exportCsvBtn')?.addEventListener('click', () => {
    const rows = lastJsonData?.data;
    if (!rows?.length) {
      // Fallback: scrape table DOM if no AJAX data cached yet
      exportFromDom();
      return;
    }
    const headers = ['Subscriber','Type','Rep','Email','Plan','Amount','Status','Subscribed','Expires','TXN'];
    const lines = [
      headers.join(','),
      ...rows.map(s => [
        `"${(s.subscriber_name ?? '').replace(/"/g,'""')}"`,
        s.subscriber_type,
        `"${(s.rep_name ?? '').replace(/"/g,'""')}"`,
        s.subscriber_email ?? '',
        s.plan_key,
        s.amount_fmt,
        s.subscription_status,
        s.transaction_date_fmt,
        s.expiration_fmt ?? '',
        s.transaction_number ?? '',
      ].join(','))
    ];
    downloadCsv(lines.join('\n'), `subscribers_${new Date().toISOString().slice(0,10)}.csv`);
  });
}

function exportFromDom() {
  const tbl = document.getElementById('subscriberTable');
  if (!tbl) { alert('No data to export.'); return; }
  const rows = [...tbl.querySelectorAll('tbody tr')].map(tr =>
    [...tr.querySelectorAll('td')].map(td => `"${td.innerText.trim().replace(/\n+/g,' ').replace(/"/g,'""')}"`)
        .join(',')
  );
  downloadCsv(['Subscriber,Plan,Amount,Status,Subscribed,Expires,TXN', ...rows].join('\n'),
    `subscribers_${new Date().toISOString().slice(0,10)}.csv`);
}

function downloadCsv(content, filename) {
  const a = Object.assign(document.createElement('a'), {
    href: URL.createObjectURL(new Blob([content], { type: 'text/csv' })),
    download: filename,
  });
  a.click();
  URL.revokeObjectURL(a.href);
}

// ── Boot ────────────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
  // KPI counters
  document.querySelectorAll('.stat-counter').forEach(animateCounter);

  // Tier bars
  initTierBars();

  // Revenue chart (initial load from canvas data attrs)
  initRevenueChart();
  initTierButtons();

  // Subscriber table
  attachPageBtnListeners();
  initFilterControls();
  initExport();

  // ── Global Date Filter ─────────────────────────────────────────────────
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

  function refreshSubscriptionData(dateFrom, dateTo) {
    const loading = document.getElementById('filterLoading');
    if (loading) loading.classList.remove('hidden');

    const params = new URLSearchParams();
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo)   params.set('date_to', dateTo);

    fetch('/admin/analytics/subscription-data?' + params.toString())
      .then(r => r.json())
      .then(data => {
        // Update KPI cards (3 stat-counters: Total, Active, Expiring)
        const sm = data.subscriptionMetrics;
        const counters = document.querySelectorAll('.stat-counter');
        const targets = [sm.total, sm.active, sm.expiring];
        counters.forEach((el, i) => {
          if (targets[i] !== null && targets[i] !== undefined) {
            el.dataset.target = targets[i];
            el.textContent = targets[i];
          }
        });
        // Update revenue text
        const revVal = document.getElementById('revenueValue');
        if (revVal) revVal.textContent = '₱' + sm.revenue.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        // Update expired count
        const expText = document.getElementById('expiredCount');
        if (expText) expText.textContent = sm.expired + ' already expired';

        // Update revenue chart
        const rv = data.subscriptionRevenue;
        if (rv) {
          buildRevenueChart(rv.months, rv.currentYearData, rv.previousYearData, rv.currentYear, rv.previousYear);
          const sub = document.getElementById('revenueSubtitle');
          if (sub) sub.textContent = rv.dateRange + ' · Current vs Previous Year';
        }

        // Update tier bars
        const st = data.subscriptionTiers;
        if (st) {
          const tierStyles = [
            { text: 'text-yellow-700' },
            { text: 'text-blue-700' },
            { text: 'text-orange-700' },
          ];
          const tierBoxes = document.querySelectorAll('.tier-bar');
          st.tiers.forEach((tier, i) => {
            const w = st.maxCount > 0 ? Math.round((tier.count / st.maxCount) * 100) : 0;
            const pct = st.total > 0 ? ((tier.count / st.total) * 100).toFixed(1) : '0';
            if (tierBoxes[i]) {
              tierBoxes[i].dataset.width = w;
              tierBoxes[i].style.width = w + '%';
            }
            const parent = tierBoxes[i]?.closest('.rounded-xl');
            if (parent) {
              const countEl = parent.querySelector('.text-lg');
              if (countEl) countEl.textContent = tier.count;
              const pctEl = parent.querySelector('.text-xs');
              if (pctEl) pctEl.textContent = pct + '% of all';
            }
          });
          const totalEl = document.querySelector('.border-t.border-gray-100 .text-xl');
          if (totalEl) totalEl.textContent = st.total;
        }

        if (loading) loading.classList.add('hidden');
      })
      .catch(err => {
        console.error('Subscription filter error:', err);
        if (loading) loading.classList.add('hidden');
      });
  }

  document.querySelectorAll('.date-preset-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.date-preset-btn').forEach(b => {
        b.classList.remove('active', 'border-indigo-500', 'text-white', 'bg-indigo-500', 'font-semibold');
        b.classList.add('border-gray-200', 'text-gray-600', 'font-medium');
      });
      this.classList.add('active', 'border-indigo-500', 'text-white', 'bg-indigo-500', 'font-semibold');
      this.classList.remove('border-gray-200', 'text-gray-600', 'font-medium');
      const range = getDateRange(this.dataset.range);
      document.getElementById('globalDateFrom').value = range.from;
      document.getElementById('globalDateTo').value   = range.to;
      refreshSubscriptionData(range.from, range.to);
    });
  });

  document.getElementById('applyGlobalDateFilter')?.addEventListener('click', function () {
    document.querySelectorAll('.date-preset-btn').forEach(b => {
      b.classList.remove('active', 'border-indigo-500', 'text-white', 'bg-indigo-500', 'font-semibold');
      b.classList.add('border-gray-200', 'text-gray-600', 'font-medium');
    });
    refreshSubscriptionData(
      document.getElementById('globalDateFrom').value,
      document.getElementById('globalDateTo').value
    );
  });
});