// Initialize Active Users Chart
let activeChart = null;

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
  miniCards.forEach(card => {
    const canvas = card.querySelector('.mini-chart');
    if (!canvas) return;

    const months = JSON.parse(card.dataset.months || '[]');
    const data = JSON.parse(card.dataset.data || '[]');
    const pct = parseFloat(card.dataset.pct || '0');

    const color = pct >= 0 ? '#10b981' : '#ef4444';

    // create tiny chart
    new Chart(canvas, {
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

    // color percentage text
    const pctEl = card.querySelector('.mini-change');
    if (pctEl) {
      pctEl.style.color = pct >= 0 ? '#059669' : '#dc2626';
    }
  });

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
        // Remove active class from all items
        dropdownItems.forEach(i => i.classList.remove('active'));
        // Add active class to clicked item
        this.classList.add('active');
        
        const range = this.dataset.range;
        const rangeText = this.textContent;
        
        // Update displayed date range
        document.querySelector('.earnings-date-range').textContent = rangeText;
        
        // Close dropdown
        earningsDropdownMenu.classList.remove('active');
        earningsDropdownBtn.classList.remove('active');
        
        // Fetch new data and update chart
        fetchEarningsData(range);
      });
    });
  }

  // Function to fetch earnings data for selected range
  function fetchEarningsData(range) {
    // Show loading state
    const earningsTotal = document.querySelector('.earnings-total-amount');
    const originalText = earningsTotal.textContent;
    earningsTotal.style.opacity = '0.5';

    // Calculate date range
    const today = new Date();
    let startDate, endDate;
    
    switch(range) {
      case 'today':
        startDate = endDate = today.toISOString().split('T')[0];
        break;
      case 'yesterday':
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);
        startDate = endDate = yesterday.toISOString().split('T')[0];
        break;
      case 'last7days':
        const last7 = new Date(today);
        last7.setDate(last7.getDate() - 7);
        startDate = last7.toISOString().split('T')[0];
        endDate = today.toISOString().split('T')[0];
        break;
      case 'thismonth':
        startDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
        endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
        break;
      case 'lastmonth':
        startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1).toISOString().split('T')[0];
        endDate = new Date(today.getFullYear(), today.getMonth(), 0).toISOString().split('T')[0];
        break;
      case 'last3months':
        const last3m = new Date(today);
        last3m.setMonth(last3m.getMonth() - 3);
        startDate = last3m.toISOString().split('T')[0];
        endDate = today.toISOString().split('T')[0];
        break;
      case 'thisyear':
        startDate = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
        endDate = new Date(today.getFullYear(), 11, 31).toISOString().split('T')[0];
        break;
      default:
        startDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
        endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
    }

    // Fetch data via AJAX (you'll need to create a route for this)
    fetch(`/admin/dashboard/earnings?start=${startDate}&end=${endDate}`)
      .then(response => response.json())
      .then(data => {
        // Update chart
        if (window.earningsChartInstance) {
          window.earningsChartInstance.data.labels = data.days;
          window.earningsChartInstance.data.datasets[0].data = data.data;
          window.earningsChartInstance.update();
        }
        
        // Update total
        earningsTotal.textContent = '₱' + parseFloat(data.total).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        earningsTotal.style.opacity = '1';
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
