// Initialize Active Users Chart
let activeChart = null;

// Registry of all mini sparkline Chart instances keyed by their container index
const miniChartInstances = [];

/** Escape a value for safe insertion into HTML */
function escHtml(str) {
  return String(str == null ? '' : str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

document.addEventListener('DOMContentLoaded', function() {
  const ctx = document.getElementById('activeUsersChart');
  
  if (ctx) {
    // Get chart data from data attributes
    const chartLabels = JSON.parse(ctx.dataset.months || '[]');
    const chartValues = JSON.parse(ctx.dataset.data || '[]');
    
    const chartData = {
      labels: chartLabels,
      datasets: [{
        label: 'Active Users',
        data: chartValues,
        borderColor: '#6366f1',
        backgroundColor: 'rgba(99, 102, 241, 0.1)',
        borderWidth: 3,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#6366f1',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        pointRadius: 5,
        pointHoverRadius: 7,
      }]
    };

    activeChart = new Chart(ctx, {
      type: 'line',
      data: chartData,
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            border: {
              display: false,
            },
            grid: {
              color: '#e5e7eb',
              drawBorder: false,
            },
          },
          x: {
            border: {
              display: false,
            },
            grid: {
              display: false,
            },
          },
        },
      },
    });
  }

  // Handle stat card clicks to update chart and left sidebar stats
  const statCards = document.querySelectorAll('.stat-card[data-chart-type]');
  const totalNumberEl = document.querySelector('.total-number');
  const statsListEl = document.querySelector('.stats-list');

  statCards.forEach(card => {
    card.addEventListener('click', function() {
      const months = JSON.parse(this.dataset.months || '[]');
      const data = JSON.parse(this.dataset.data || '[]');
      const label = this.dataset.label || 'Chart Data';
      const breakdown = JSON.parse(this.dataset.breakdown || '{}');

      if (activeChart) {
        // Update chart data
        activeChart.data.labels = months;
        activeChart.data.datasets[0].label = label;
        activeChart.data.datasets[0].data = data;
        activeChart.update();

        // Visual feedback: highlight active card
        statCards.forEach(c => c.style.borderBottom = 'none');
        this.style.borderBottom = '3px solid #6366f1';
      }

      // Update left sidebar stats with breakdown data
      if (breakdown && breakdown.total !== undefined) {
        totalNumberEl.textContent = breakdown.total;
        
        // Update contractors and property owners counts
        const contractorItem = statsListEl.querySelector('.stat-item:nth-child(1)');
        const propertyOwnerItem = statsListEl.querySelector('.stat-item:nth-child(2)');
        
        if (contractorItem) {
          contractorItem.querySelector('.stat-value').textContent = breakdown.contractors;
        }
        if (propertyOwnerItem) {
          propertyOwnerItem.querySelector('.stat-value').textContent = breakdown.property_owners;
        }
      }
    });
  });

  // Initialize mini sparkline charts for the new mini stat cards
  const miniCards = document.querySelectorAll('.mini-stat-card');
  miniCards.forEach((card, idx) => {
    const canvas = card.querySelector('.mini-chart');
    if (!canvas) return;

    const months = JSON.parse(card.dataset.months || '[]');
    const data = JSON.parse(card.dataset.data || '[]');
    const pct = parseFloat(card.dataset.pct || '0');

    const color = pct >= 0 ? '#10b981' : '#ef4444';

    const instance = new Chart(canvas, {
      type: 'line',
      data: {
        labels: months,
        datasets: [{
          data: data,
          borderColor: color,
          backgroundColor: 'transparent',
          borderWidth: 2,
          pointRadius: 0,
          tension: 0.35,
          fill: false,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { display: false },
          y: { display: false }
        },
        elements: { line: { capBezierPoints: true } }
      }
    });

    miniChartInstances[idx] = instance;

    // color percentage text
    const pctEl = card.querySelector('.mini-change');
    if (pctEl) {
      pctEl.style.color = pct >= 0 ? '#059669' : '#dc2626';
    }
  });

  // ── Global Date Filter ───────────────────────────────────────────────────
  const globalFilterBtns = document.querySelectorAll('#globalFilterOptions .global-filter-btn');
  const globalFilterLoading = document.getElementById('globalFilterLoading');
  const customRangeBtn     = document.getElementById('globalCustomRangeBtn');
  const customPicker       = document.getElementById('globalCustomPicker');
  const customRangeStart   = document.getElementById('customRangeStart');
  const customRangeEnd     = document.getElementById('customRangeEnd');
  let currentRange = 'thisyear';

  // Seed default values (current year)
  if (customRangeStart && customRangeEnd) {
    const now  = new Date();
    const yyyy = now.getFullYear();
    const pad  = n => String(n).padStart(2, '0');
    customRangeStart.value = yyyy + '-01-01';
    customRangeEnd.value   = yyyy + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate());
    // Keep date inputs within valid bounds
    const todayStr = yyyy + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate());
    customRangeEnd.max = todayStr;
  }

  // Preset pill buttons
  globalFilterBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      const range = this.dataset.range;
      if (range === currentRange) return;

      globalFilterBtns.forEach(b => b.classList.remove('active'));
      if (customRangeBtn) customRangeBtn.classList.remove('active');
      this.classList.add('active');
      currentRange = range;

      if (customPicker) customPicker.classList.remove('open');
      if (globalFilterLoading) globalFilterLoading.classList.add('visible');

      fetchDashboardData(range);
    });
  });

  // Custom range picker toggle
  if (customRangeBtn && customPicker) {
    customRangeBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      customPicker.classList.toggle('open');
    });

    document.getElementById('customPickerCancel').addEventListener('click', function (e) {
      e.stopPropagation();
      customPicker.classList.remove('open');
    });

    document.getElementById('customPickerApply').addEventListener('click', function (e) {
      e.stopPropagation();
      var start = customRangeStart.value;
      var end   = customRangeEnd.value;

      if (!start || !end) {
        alert('Please select both a start and an end date.');
        return;
      }
      if (start > end) {
        alert('"From" date must be before or equal to "To" date.');
        return;
      }

      // Activate the custom button, deactivate presets
      globalFilterBtns.forEach(b => b.classList.remove('active'));
      customRangeBtn.classList.add('active');
      currentRange = 'custom';

      customPicker.classList.remove('open');
      if (globalFilterLoading) globalFilterLoading.classList.add('visible');

      fetchDashboardData('custom', start, end);
    });

    // Close picker when clicking anywhere outside
    document.addEventListener('click', function (e) {
      if (customPicker && !customPicker.contains(e.target) && e.target !== customRangeBtn) {
        customPicker.classList.remove('open');
      }
    });
  }

  /**
   * Fetch all chart data for the selected range and update every chart on the page.
   * For named ranges pass range key; for custom pass range='custom' + startDate + endDate.
   */
  function fetchDashboardData(range, startDate, endDate) {
    var url;
    if (range === 'custom' && startDate && endDate) {
      url = '/admin/dashboard/data?start=' + encodeURIComponent(startDate)
          + '&end=' + encodeURIComponent(endDate);
    } else {
      url = '/admin/dashboard/data?range=' + encodeURIComponent(range);
    }
    fetch(url)
      .then(function (response) {
        if (!response.ok) throw new Error('Network error: ' + response.status);
        return response.json();
      })
      .then(function (payload) {
        updateAllCharts(payload);
      })
      .catch(function (err) {
        console.error('[Dashboard] Failed to load dashboard data:', err);
      })
      .finally(function () {
        if (globalFilterLoading) globalFilterLoading.classList.remove('visible');
      });
  }

  /**
   * Apply fresh server payload to every chart and stat on the page.
   */
  function updateAllCharts(payload) {
    // ── 1. Mini stat cards (Projects / Bids / Revenue) ──────────────────────
    const miniCardDefs = [
      { key: 'projectsMetrics',   isCurrency: false },
      { key: 'activeBidsMetrics', isCurrency: false },
      { key: 'revenueMetrics',    isCurrency: true  },
    ];

    const miniCardEls = document.querySelectorAll('.mini-stat-card');
    miniCardDefs.forEach(function (def, idx) {
      const metrics = payload[def.key];
      if (!metrics || !miniCardEls[idx]) return;

      const card    = miniCardEls[idx];
      const pct     = parseFloat(metrics.pctChange || 0);
      const color   = pct >= 0 ? '#10b981' : '#ef4444';

      // Update data attributes (for re-use)
      card.dataset.months = JSON.stringify(metrics.months);
      card.dataset.data   = JSON.stringify(metrics.data);
      card.dataset.pct    = pct;

      // Update displayed number
      const numEl = card.querySelector('.mini-number');
      if (numEl) {
        numEl.textContent = def.isCurrency
          ? '₱' + parseFloat(metrics.total).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
          : metrics.total;
      }

      // Update pct change text
      const pctEl = card.querySelector('.mini-change');
      if (pctEl) {
        pctEl.textContent = (pct >= 0 ? '+' : '') + pct + '%';
        pctEl.style.color = pct >= 0 ? '#059669' : '#dc2626';
      }

      // Update mini sparkline chart
      const chartInst = miniChartInstances[idx];
      if (chartInst) {
        chartInst.data.labels                       = metrics.months;
        chartInst.data.datasets[0].data             = metrics.data;
        chartInst.data.datasets[0].borderColor      = color;
        chartInst.update('none');
      }
    });

    // ── 2. Active Users chart + left-panel numbers ───────────────────────────
    const activeData = payload.activeUsersData;
    if (activeData && activeChart) {
      // Use the activeUsersData series (monthly registration trend for the period)
      if (activeData.months && activeData.data) {
        activeChart.data.labels               = activeData.months;
        activeChart.data.datasets[0].data     = activeData.data;
        activeChart.data.datasets[0].label    = 'Active Users';
        activeChart.update('none');
      }

      // Update left panel count numbers
      const totalNumEl = document.querySelector('.total-number');
      if (totalNumEl) totalNumEl.textContent = activeData.total;

      const statItems = document.querySelectorAll('.stats-list .stat-item');
      if (statItems[0]) statItems[0].querySelector('.stat-value').textContent = activeData.contractors;
      if (statItems[1]) statItems[1].querySelector('.stat-value').textContent = activeData.property_owners;
    }

    // ── 3. "Big" stat cards (Total / New / Active users, Pending Reviews) ───
    const statCardDefs = [
      { chartType: 'total-users',     payloadKey: 'totalUsersChartData',     countKey: 'totalUsers',    breakdownKey: 'totalUsersBreakdown'     },
      { chartType: 'new-users',       payloadKey: 'newUsersChartData',        countKey: 'newUsers',      breakdownKey: 'newUsersBreakdown'       },
      { chartType: 'active-users',    payloadKey: 'activeUsersChartData',     countKey: 'activeUsers',   breakdownKey: 'activeUsersBreakdown'    },
      { chartType: 'pending-reviews', payloadKey: 'pendingReviewsChartData',  countKey: 'pendingReviews',breakdownKey: 'pendingReviewsBreakdown' },
    ];

    statCardDefs.forEach(function (def) {
      const cardEl   = document.querySelector('.stat-card[data-chart-type="' + def.chartType + '"]');
      const chartCD  = payload[def.payloadKey];
      const stats    = payload.dashboardStats;
      const breakdown= payload[def.breakdownKey];

      if (!cardEl || !chartCD) return;

      // Update data attributes so click-to-expand still works
      cardEl.dataset.months    = JSON.stringify(chartCD.months);
      cardEl.dataset.data      = JSON.stringify(chartCD.data);
      cardEl.dataset.label     = chartCD.label;
      if (breakdown) cardEl.dataset.breakdown = JSON.stringify(breakdown);

      // Update visible number in the card
      if (stats && stats[def.countKey] !== undefined) {
        const numEl = cardEl.querySelector('.stat-card-number');
        if (numEl) numEl.textContent = stats[def.countKey];
      }
    });

    // If the active-users chart is currently showing one of the stat-card views,
    // also refresh it with the new data matching the active tab
    const highlightedCard = document.querySelector('.stat-card[style*="border-bottom"]');
    if (highlightedCard && activeChart) {
      const months    = JSON.parse(highlightedCard.dataset.months || '[]');
      const chartData = JSON.parse(highlightedCard.dataset.data   || '[]');
      const label     = highlightedCard.dataset.label || '';
      activeChart.data.labels               = months;
      activeChart.data.datasets[0].data     = chartData;
      activeChart.data.datasets[0].label    = label;
      activeChart.update('none');
    }

    // ── 4. Top Contractors list ────────────────────────────────────────────
    var contractorsContainer = document.querySelector('[data-list="top-contractors"]');
    if (contractorsContainer && Array.isArray(payload.topContractors)) {
      if (!payload.topContractors.length) {
        contractorsContainer.innerHTML = '<p class="empty-state">No contractors found for this period</p>';
      } else {
        contractorsContainer.innerHTML = payload.topContractors.map(function (c) {
          var initial = c.company_name ? c.company_name.charAt(0).toUpperCase() : '?';
          var avatar  = (c.profile_pic && c.profile_pic.trim())
            ? '<img src="' + escHtml((window.storageBaseUrl || '/storage') + '/' + c.profile_pic) + '" alt="' + escHtml(c.company_name) + '">'
            : escHtml(initial);
          var count = (c.period_count !== undefined && c.period_count !== null) ? c.period_count : c.completed_projects;
          return '<div class="item-card">'
            + '<div class="item-left">'
            +   '<div class="item-avatar avatar-contractor">' + avatar + '</div>'
            +   '<div class="item-info">'
            +     '<h3 class="item-name">' + escHtml(c.company_name) + '</h3>'
            +     '<p class="item-type">' + escHtml(c.type_name) + '</p>'
            +   '</div>'
            + '</div>'
            + '<div class="item-right">'
            +   '<p class="item-count">' + count + '</p>'
            +   '<p class="item-label">Projects in Period</p>'
            + '</div>'
            + '</div>';
        }).join('');
      }
    }

    // ── 5. Top Property Owners list ───────────────────────────────────────
    var ownersContainer = document.querySelector('[data-list="top-owners"]');
    if (ownersContainer && Array.isArray(payload.topPropertyOwners)) {
      if (!payload.topPropertyOwners.length) {
        ownersContainer.innerHTML = '<p class="empty-state">No property owners found for this period</p>';
      } else {
        ownersContainer.innerHTML = payload.topPropertyOwners.map(function (o) {
          var name    = escHtml(((o.first_name || '') + ' ' + (o.last_name || '')).trim());
          var initial = o.first_name ? o.first_name.charAt(0).toUpperCase() : '?';
          var avatar  = (o.profile_pic && o.profile_pic.trim())
            ? '<img src="' + escHtml((window.storageBaseUrl || '/storage') + '/' + o.profile_pic) + '" alt="' + name + '">'
            : escHtml(initial);
          return '<div class="item-card">'
            + '<div class="item-left">'
            +   '<div class="item-avatar avatar-owner">' + avatar + '</div>'
            +   '<div class="item-info">'
            +     '<h3 class="item-name">' + name + '</h3>'
            +     '<p class="item-type">Property Owner</p>'
            +   '</div>'
            + '</div>'
            + '<div class="item-right">'
            +   '<p class="item-count">' + (o.completed_projects || 0) + '</p>'
            +   '<p class="item-label">Projects in Period</p>'
            + '</div>'
            + '</div>';
        }).join('');
      }
    }

    // ── 6. Top Projects with Bids table ───────────────────────────────────
    var projectsTbody = document.getElementById('topProjectsTbody');
    if (projectsTbody && Array.isArray(payload.topProjects)) {
      if (!payload.topProjects.length) {
        projectsTbody.innerHTML = '<tr><td colspan="4" class="empty-state">No projects found for this period</td></tr>';
      } else {
        projectsTbody.innerHTML = payload.topProjects.map(function (p) {
          var initial     = p.project_title ? p.project_title.charAt(0).toUpperCase() : '?';
          var statusClass = escHtml((p.project_status || '').toString().toLowerCase());
          return '<tr>'
            + '<td><div class="project-info">'
            +   '<span class="project-avatar">' + escHtml(initial) + '</span>'
            +   '<span class="project-name">'  + escHtml(p.project_title || '') + '</span>'
            + '</div></td>'
            + '<td><span class="project-owner">' + escHtml(((p.first_name || '') + ' ' + (p.last_name || '')).trim()) + '</span></td>'
            + '<td><span class="project-bid-count">' + (p.bid_count || 0) + '</span></td>'
            + '<td><span class="project-status ' + statusClass + '">' + escHtml(p.status_label || '') + '</span></td>'
            + '</tr>';
        }).join('');
      }
    }

    // ── 7. Earnings chart & totals ─────────────────────────────────────────
    var earningsMx = payload.earningsMetrics;
    if (earningsMx && window.earningsChartInstance) {
      window.earningsChartInstance.data.labels           = earningsMx.days || [];
      window.earningsChartInstance.data.datasets[0].data = earningsMx.data || [];

      // Adapt tooltip title: monthly/cross-month ranges show label as-is; daily show "Day X"
      window.earningsChartInstance.options.plugins.tooltip.callbacks.title =
        (earningsMx.format === 'monthly' || earningsMx.format === 'dated')
          ? function (ctx) { return ctx[0].label; }
          : function (ctx) { return 'Day ' + ctx[0].label; };

      window.earningsChartInstance.update('none');

      var earningsTotalEl = document.querySelector('.earnings-total-amount');
      if (earningsTotalEl) {
        earningsTotalEl.textContent = '₱' + parseFloat(earningsMx.total || 0)
          .toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      }

      var earningsDateRangeEl = document.querySelector('.earnings-date-range');
      if (earningsDateRangeEl && earningsMx.dateRange) {
        earningsDateRangeEl.textContent = earningsMx.dateRange;
      }
    }
  }
  // ── End Global Date Filter ───────────────────────────────────────────────

  // Earnings Dropdown Toggle
  const earningsDropdownBtn = document.getElementById('earningsDropdownBtn');
  const earningsDropdownMenu = document.getElementById('earningsDropdownMenu');
  
  if (earningsDropdownBtn && earningsDropdownMenu) {
    earningsDropdownBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      earningsDropdownMenu.classList.toggle('active');
      earningsDropdownBtn.classList.toggle('active');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!earningsDropdownMenu.contains(e.target) && !earningsDropdownBtn.contains(e.target)) {
        earningsDropdownMenu.classList.remove('active');
        earningsDropdownBtn.classList.remove('active');
      }
    });

    // Handle dropdown item selection
    const dropdownItems = earningsDropdownMenu.querySelectorAll('.earnings-dropdown-item');
    dropdownItems.forEach(item => {
      item.addEventListener('click', function() {
        dropdownItems.forEach(i => i.classList.remove('active'));
        this.classList.add('active');

        const range = this.dataset.range;

        // Close dropdown
        earningsDropdownMenu.classList.remove('active');
        earningsDropdownBtn.classList.remove('active');

        // Date-range text and chart are updated by fetchEarningsData once the server responds
        fetchEarningsData(range);
      });
    });
  }

  // Function to fetch earnings data for selected range
  // Sends the named range key to the server — no JS date arithmetic needed.
  function fetchEarningsData(range) {
    const earningsTotal = document.querySelector('.earnings-total-amount');
    const originalText  = earningsTotal.textContent;
    earningsTotal.style.opacity = '0.5';

    fetch('/admin/dashboard/earnings?range=' + encodeURIComponent(range))
      .then(response => response.json())
      .then(data => {
        if (window.earningsChartInstance) {
          window.earningsChartInstance.data.labels           = data.days || [];
          window.earningsChartInstance.data.datasets[0].data = data.data || [];

          // Tooltip title: day numbers → "Day X"; date/month strings → label as-is
          window.earningsChartInstance.options.plugins.tooltip.callbacks.title =
            (data.format === 'monthly' || data.format === 'dated')
              ? function (ctx) { return ctx[0].label; }
              : function (ctx) { return 'Day ' + ctx[0].label; };

          window.earningsChartInstance.update('none');
        }

        earningsTotal.textContent = '₱' + parseFloat(data.total || 0)
          .toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        earningsTotal.style.opacity = '1';

        var dateRangeEl = document.querySelector('.earnings-date-range');
        if (dateRangeEl && data.dateRange) dateRangeEl.textContent = data.dateRange;
      })
      .catch(error => {
        console.error('Error fetching earnings data:', error);
        earningsTotal.textContent = originalText;
        earningsTotal.style.opacity = '1';
      });
  }

  // Initialize Earnings Chart
  const earningsCtx = document.getElementById('earningsChart');
  if (earningsCtx) {
    const days = JSON.parse(earningsCtx.dataset.days || '[]');
    const earningsData = JSON.parse(earningsCtx.dataset.data || '[]');

    window.earningsChartInstance = new Chart(earningsCtx, {
      type: 'line',
      data: {
        labels: days,
        datasets: [{
          label: 'Earnings',
          data: earningsData,
          borderColor: '#ffffff',
          backgroundColor: 'rgba(255, 255, 255, 0.2)',
          borderWidth: 2.5,
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#ffffff',
          pointBorderColor: 'rgba(255, 255, 255, 0.3)',
          pointBorderWidth: 3,
          pointRadius: 4,
          pointHoverRadius: 6,
          pointHoverBackgroundColor: '#ffffff',
          pointHoverBorderColor: '#ff5e62',
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
            titleColor: '#1f2937',
            bodyColor: '#1f2937',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 12,
            displayColors: false,
            callbacks: {
              title: function(context) {
                return 'Day ' + context[0].label;
              },
              label: function(context) {
                return '₱' + context.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            border: {
              display: false,
            },
            grid: {
              color: 'rgba(255, 255, 255, 0.15)',
              drawBorder: false,
            },
            ticks: {
              color: 'rgba(255, 255, 255, 0.8)',
              font: {
                size: 11
              },
              callback: function(value) {
                return '₱' + value.toLocaleString();
              }
            }
          },
          x: {
            border: {
              display: false,
            },
            grid: {
              display: false,
            },
            ticks: {
              color: 'rgba(255, 255, 255, 0.8)',
              font: {
                size: 11
              },
              maxRotation: 0,
              autoSkip: true,
              maxTicksLimit: 15
            }
          },
        },
      },
    });
  }

  // notifications dropdown moved to mainComponents.js
});
