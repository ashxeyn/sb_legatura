/**
 * userActivity_Analytics.js
 *
 * NOTE: Charts (userGrowthChart, userDistributionChart) are created in the
 *       inline <script> of the blade template with REAL DB data.
 *       This file only handles utility features — no chart creation here.
 */

'use strict';

// Animate numbers on page load
function animateValue(element, start, end, duration) {
  if (!element) return;

  let startTimestamp = null;
  const step = (timestamp) => {
    if (!startTimestamp) startTimestamp = timestamp;
    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
    const value = Math.floor(progress * (end - start) + start);
    element.textContent = value.toLocaleString();
    if (progress < 1) {
      window.requestAnimationFrame(step);
    }
  };
  window.requestAnimationFrame(step);
}

// Trigger animations when page loads
window.addEventListener('load', () => {
  // Animate stat card numbers
  const statNumbers = document.querySelectorAll('.text-4xl, .text-2xl');
  statNumbers.forEach(num => {
    const text = num.textContent.replace(/,/g, '');
    if (!isNaN(text)) {
      const endValue = parseInt(text);
      animateValue(num, 0, endValue, 1500);
    }
  });

  // Animate progress bars
  const progressBars = document.querySelectorAll('.bg-gradient-to-r');
  progressBars.forEach(bar => {
    const width = bar.style.width;
    bar.style.width = '0%';
    setTimeout(() => {
      bar.style.transition = 'width 1.5s ease-out';
      bar.style.width = width;
    }, 500);
  });
});

// Add table row click handlers
document.querySelectorAll('tbody tr').forEach(row => {
  row.addEventListener('click', function() {
    // Add ripple effect
    this.style.transform = 'scale(0.98)';
    setTimeout(() => {
      this.style.transform = 'scale(1)';
    }, 100);
  });
});

// Export functionality
const exportButton = document.querySelector('.relative.p-8 button.px-4.py-2.bg-gradient-to-r');
if (exportButton) {
  exportButton.addEventListener('click', function() {
    // Show loading state
    const originalText = this.innerHTML;
    this.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Exporting...';

    // Simulate export
    setTimeout(() => {
      this.innerHTML = originalText;
      alert('Export functionality coming soon!');
    }, 1000);
  });
}

// Initialize filter bindings only if inline script didn't already bind them
function initUserActivityFiltersFromBundle() {
  try {
    if (document.body && document.body.dataset && document.body.dataset.uaFiltersBound === '1') return;
  } catch (e) { /* ignore */ }

  // Minimal bindings that call existing page functions if present
  const schedule = (fn, delay = 450) => {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
  };

  const callRefresh = (from, to) => {
    if (typeof refreshUserData === 'function') return refreshUserData(from, to);
    // fallback: basic fetch to update top stats only
    const params = new URLSearchParams();
    if (from) params.set('date_from', from);
    if (to) params.set('date_to', to);
    fetch('/admin/analytics/user-data?' + params.toString(), {
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
      .then(r => {
        const ct = r.headers.get('content-type') || '';
        if (!r.ok) return r.text().then(t => { throw new Error('HTTP ' + r.status + ': ' + t); });
        if (!ct.includes('application/json')) return r.text().then(t => { console.error('Non-JSON response:', t); throw new Error('Non-JSON response'); });
        return r.json();
      }).then(data => {
        const um = data.userMetrics;
        const el = id => document.getElementById(id);
        if (el('statTotalUsers'))     el('statTotalUsers').textContent     = Number(um.total_users).toLocaleString();
        if (el('statPropertyOwners')) el('statPropertyOwners').textContent = Number(um.property_owners).toLocaleString();
        if (el('statContractors'))    el('statContractors').textContent    = Number(um.contractors).toLocaleString();
        if (el('statActiveProjects')) el('statActiveProjects').textContent = Number(um.active_projects).toLocaleString();
        if (el('statActiveUsers'))    el('statActiveUsers').textContent    = Number(um.active_users).toLocaleString();
        if (el('statSuspended'))      el('statSuspended').textContent      = Number(um.suspended_users).toLocaleString();
        if (el('statNewThisMonth'))   el('statNewThisMonth').textContent   = Number(um.new_this_month).toLocaleString();
      }).catch(() => {});
  };

  const callFetchActivity = (page = 1) => {
    if (typeof fetchActivity === 'function') return fetchActivity(page);
    // fallback basic fetch for activity panel
    const params = new URLSearchParams();
    const search = document.getElementById('activitySearch')?.value || '';
    const from   = document.getElementById('activityDateFrom')?.value || '';
    const to     = document.getElementById('activityDateTo')?.value || '';
    if (search) params.set('search', search);
    if (from) params.set('date_from', from);
    if (to) params.set('date_to', to);
    params.set('page', page);
    fetch('/admin/analytics/user-activity-feed-data?' + params.toString(), {
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
      .then(r => {
        const ct = r.headers.get('content-type') || '';
        if (!r.ok) return r.text().then(t => { throw new Error('HTTP ' + r.status + ': ' + t); });
        if (!ct.includes('application/json')) return r.text().then(t => { console.error('Non-JSON response:', t); throw new Error('Non-JSON response'); });
        return r.json();
      }).then(data => {
        if (typeof renderActivityTable === 'function') renderActivityTable(data);
        if (typeof renderActivityPagination === 'function') renderActivityPagination(data);
      }).catch(() => {});
  };

  // Global filter elements
  const gFrom = document.getElementById('globalDateFrom');
  const gTo   = document.getElementById('globalDateTo');
  const globalSchedule = schedule(() => {
    const from = gFrom?.value || '';
    const to   = gTo?.value || '';
    callRefresh(from, to);
  }, 450);

  if (gFrom && gTo) {
    gFrom.addEventListener('input', () => {
      if (gTo.value && gFrom.value && gTo.value < gFrom.value) gTo.value = gFrom.value;
      gTo.min = gFrom.value || '';
      document.querySelectorAll('.date-preset-btn').forEach(b => {
        b.classList.remove('active','border-indigo-400','text-white','bg-indigo-500','font-semibold');
        b.classList.add('border-gray-200','text-gray-500','font-medium');
      });
      globalSchedule();
    });
    gTo.addEventListener('input', () => {
      if (gFrom.value && gTo.value && gTo.value < gFrom.value) gFrom.value = gTo.value;
      globalSchedule();
    });
  }

  document.getElementById('resetGlobalDateFilter')?.addEventListener('click', function () {
    if (gFrom) gFrom.value = '';
    if (gTo) { gTo.value = ''; gTo.min = ''; }
    document.querySelectorAll('.date-preset-btn').forEach(b => {
      b.classList.remove('active','border-indigo-400','text-white','bg-indigo-500','font-semibold');
      b.classList.add('border-gray-200','text-gray-500','font-medium');
    });
    const allBtn = document.querySelector('.date-preset-btn[data-range="all"]');
    if (allBtn) {
      allBtn.classList.remove('border-gray-200','text-gray-500','font-medium');
      allBtn.classList.add('active','border-indigo-400','text-white','bg-indigo-500','font-semibold');
    }
    callRefresh('', '');
  });

  // Activity filters
  const aFrom = document.getElementById('activityDateFrom');
  const aTo   = document.getElementById('activityDateTo');
  const activitySchedule = schedule(() => callFetchActivity(1), 450);
  if (aFrom && aTo) {
    aFrom.addEventListener('input', () => {
      if (aTo.value && aFrom.value && aTo.value < aFrom.value) aTo.value = aFrom.value;
      aTo.min = aFrom.value || '';
      activitySchedule();
    });
    aTo.addEventListener('input', () => {
      if (aFrom.value && aTo.value && aTo.value < aFrom.value) aFrom.value = aTo.value;
      activitySchedule();
    });
  }

  document.getElementById('activityResetBtn')?.addEventListener('click', () => {
    const s = document.getElementById('activitySearch'); if (s) s.value = '';
    if (aFrom) { aFrom.value = ''; aFrom.min = ''; }
    if (aTo)   { aTo.value = ''; aTo.min = ''; }
    callFetchActivity(1);
  });

  // debounce search
  let activitySearchTimer;
  document.getElementById('activitySearch')?.addEventListener('input', function () {
    clearTimeout(activitySearchTimer);
    activitySearchTimer = setTimeout(() => callFetchActivity(1), 450);
  });

  // set marker so we don't rebind
  try { document.body.dataset.uaFiltersBound = '1'; } catch (e) { /* ignore */ }
}

window.addEventListener('load', initUserActivityFiltersFromBundle);
