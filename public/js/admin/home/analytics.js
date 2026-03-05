document.addEventListener('DOMContentLoaded', function () {

  // ═══════════════════════════════════════════════════════════════════
  // SECTION 1 — PROJECTS DONUT CHART
  // ═══════════════════════════════════════════════════════════════════
  const projectsCtx = document.getElementById('projectsDonutChart');

  if (projectsCtx) {
    const labels = JSON.parse(projectsCtx.dataset.labels || '[]');
    const values = JSON.parse(projectsCtx.dataset.values || '[]');

    const colors = [
      '#1f2937', // Completed - Dark Gray
      '#60a5fa', // Ongoing - Blue
      '#34d399', // On Hold - Green
      '#a78bfa'  // Cancelled - Purple
    ];

    const hoverColors = [
      '#374151',
      '#3b82f6',
      '#10b981',
      '#8b5cf6'
    ];

    const projectsChart = new Chart(projectsCtx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: colors,
          hoverBackgroundColor: hoverColors,
          borderWidth: 0,
          hoverBorderWidth: 0,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        cutout: '65%',
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(255,255,255,0.95)',
            titleColor: '#1f2937',
            bodyColor: '#1f2937',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 12,
            displayColors: true,
            callbacks: {
              label: function (context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const pct = ((context.parsed / total) * 100).toFixed(1);
                return context.label + ': ' + context.parsed + ' (' + pct + '%)';
              }
            }
          }
        },
        onHover: (event, activeElements) => {
          event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
        }
      }
    });

    // Legend interactivity
    document.querySelectorAll('.legend-item').forEach((item, index) => {
      item.addEventListener('click', function () {
        const meta = projectsChart.getDatasetMeta(0);
        meta.data[index].hidden = !meta.data[index].hidden;
        projectsChart.update();
        this.classList.toggle('active');
      });
      item.addEventListener('mouseenter', function () {
        projectsChart.setActiveElements([{ datasetIndex: 0, index }]);
        projectsChart.update();
      });
      item.addEventListener('mouseleave', function () {
        projectsChart.setActiveElements([]);
        projectsChart.update();
      });
    });
  }


  // ═══════════════════════════════════════════════════════════════════
  // SECTION 2 — PROJECT SUCCESS RATE PIE CHART
  // ═══════════════════════════════════════════════════════════════════
  const successRateCtx = document.getElementById('projectSuccessRateChart');

  if (successRateCtx) {
    const labels = JSON.parse(successRateCtx.dataset.labels || '[]');
    const values = JSON.parse(successRateCtx.dataset.values || '[]');
    const colors = JSON.parse(successRateCtx.dataset.colors || '[]');

    const hoverColors = colors.map(color => {
      const r = parseInt(color.slice(1, 3), 16);
      const g = parseInt(color.slice(3, 5), 16);
      const b = parseInt(color.slice(5, 7), 16);
      const f = 0.85;
      return '#' +
        Math.floor(r * f).toString(16).padStart(2, '0') +
        Math.floor(g * f).toString(16).padStart(2, '0') +
        Math.floor(b * f).toString(16).padStart(2, '0');
    });

    const successRateChart = new Chart(successRateCtx, {
      type: 'pie',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: colors,
          hoverBackgroundColor: hoverColors,
          borderWidth: 0,
          hoverBorderWidth: 0,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(255,255,255,0.95)',
            titleColor: '#1f2937',
            bodyColor: '#1f2937',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 12,
            displayColors: true,
            callbacks: {
              label: function (context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const pct = ((context.parsed / total) * 100).toFixed(1);
                return context.label + ': ' + context.parsed + ' (' + pct + '%)';
              }
            }
          }
        },
        onHover: (event, activeElements) => {
          event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
        }
      }
    });

    document.querySelectorAll('.success-rate-legend-item').forEach((item, index) => {
      item.addEventListener('click', function () {
        const meta = successRateChart.getDatasetMeta(0);
        meta.data[index].hidden = !meta.data[index].hidden;
        successRateChart.update();
        this.classList.toggle('active');
      });
      item.addEventListener('mouseenter', function () {
        successRateChart.setActiveElements([{ datasetIndex: 0, index }]);
        successRateChart.update();
      });
      item.addEventListener('mouseleave', function () {
        successRateChart.setActiveElements([]);
        successRateChart.update();
      });
    });
  }


  // ═══════════════════════════════════════════════════════════════════
  // SECTION 3 — PROJECTS TIMELINE LINE CHART
  // ═══════════════════════════════════════════════════════════════════
  const timelineCtx = document.getElementById('projectsTimelineChart');
  let projectsTimelineChart = null;

  if (timelineCtx) {
    const months            = JSON.parse(timelineCtx.dataset.months    || '[]');
    const newProjects       = JSON.parse(timelineCtx.dataset.new       || '[]');
    const completedProjects = JSON.parse(timelineCtx.dataset.completed || '[]');

    projectsTimelineChart = new Chart(timelineCtx, {
      type: 'line',
      data: {
        labels: months,
        datasets: [
          {
            label: 'New Projects',
            data: newProjects,
            borderColor: '#fb923c',
            backgroundColor: 'rgba(251,146,60,0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#fb923c',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
            pointHoverBackgroundColor: '#fb923c',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 3,
          },
          {
            label: 'Completed Projects',
            data: completedProjects,
            borderColor: '#818cf8',
            backgroundColor: 'rgba(129,140,248,0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#818cf8',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
            pointHoverBackgroundColor: '#818cf8',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 3,
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(255,255,255,0.95)',
            titleColor: '#1f2937',
            bodyColor: '#1f2937',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 12,
            displayColors: true,
            boxWidth: 10,
            boxHeight: 10,
            usePointStyle: true,
            callbacks: {
              title:  ctx => ctx[0].label,
              label:  ctx => ctx.dataset.label + ': ' + ctx.parsed.y + ' Projects'
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            border: { display: false },
            grid: { color: '#f3f4f6', drawBorder: false },
            ticks: { color: '#9ca3af', font: { size: 12 }, stepSize: 2 }
          },
          x: {
            border: { display: false },
            grid: { display: false },
            ticks: { color: '#9ca3af', font: { size: 12 } }
          }
        }
      }
    });

    // Dropdown toggle
    const dropdownBtn  = document.getElementById('timelineDropdownBtn');
    const dropdownMenu = document.getElementById('timelineDropdownMenu');

    if (dropdownBtn && dropdownMenu) {
      dropdownBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        dropdownMenu.classList.toggle('active');
        dropdownBtn.classList.toggle('active');
      });

      document.addEventListener('click', function (e) {
        if (!dropdownMenu.contains(e.target) && !dropdownBtn.contains(e.target)) {
          dropdownMenu.classList.remove('active');
          dropdownBtn.classList.remove('active');
        }
      });

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

    // Legend toggle
    document.querySelectorAll('.timeline-legend-item').forEach(item => {
      item.addEventListener('click', function () {
        const idx  = parseInt(this.dataset.dataset);
        const meta = projectsTimelineChart.getDatasetMeta(idx);
        meta.hidden = !meta.hidden;
        projectsTimelineChart.update();
        this.classList.toggle('hidden');
      });
    });
  }

  function fetchTimelineData(range) {
    const container = document.querySelector('.timeline-chart-container');
    if (container) container.style.opacity = '0.5';

    fetch(`/admin/analytics/timeline?range=${range}`)
      .then(r => r.json())
      .then(data => {
        if (projectsTimelineChart) {
          projectsTimelineChart.data.labels            = data.months;
          projectsTimelineChart.data.datasets[0].data  = data.newProjects;
          projectsTimelineChart.data.datasets[1].data  = data.completedProjects;
          projectsTimelineChart.update();
        }
        const rangeEl = document.querySelector('.timeline-date-range');
        if (rangeEl) rangeEl.textContent = data.dateRange;
        if (container) container.style.opacity = '1';
      })
      .catch(err => {
        console.error('Timeline fetch error:', err);
        if (container) container.style.opacity = '1';
      });
  }


  // ═══════════════════════════════════════════════════════════════════
  // SECTION 4 — PERFORMANCE KPI: ANIMATED COUNTERS + PROGRESS BARS
  // ═══════════════════════════════════════════════════════════════════
  function animateCounters() {
    document.querySelectorAll('.perf-counter').forEach(el => {
      const target    = parseFloat(el.dataset.target) || 0;
      const isFloat   = target % 1 !== 0;
      const duration  = 1400;
      const stepMs    = 16;
      const steps     = duration / stepMs;
      const increment = target / steps;
      let current     = 0;

      const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
          current = target;
          clearInterval(timer);
        }
        el.textContent = isFloat
          ? current.toFixed(1)
          : Math.floor(current).toLocaleString();
      }, stepMs);
    });
  }

  function animateProgressBars() {
    document.querySelectorAll('.progress-bar-fill').forEach(el => {
      setTimeout(() => {
        el.style.width = (el.dataset.width || 0) + '%';
      }, 200);
    });
  }

  // Trigger animations when KPI cards scroll into view
  const firstPerfCard = document.querySelector('.perf-card');
  if (firstPerfCard) {
    const perfObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCounters();
          animateProgressBars();
          perfObserver.disconnect();
        }
      });
    }, { threshold: 0.1 });

    perfObserver.observe(firstPerfCard);
  }


  // ═══════════════════════════════════════════════════════════════════
  // SECTION 5 — COMPLETION TRENDS BAR CHART (12-month)
  // ═══════════════════════════════════════════════════════════════════
  const trendsCanvas = document.getElementById('completionTrendsChart');

  if (trendsCanvas) {
    const months   = JSON.parse(trendsCanvas.dataset.months    || '[]');
    const newData  = JSON.parse(trendsCanvas.dataset.new       || '[]');
    const compData = JSON.parse(trendsCanvas.dataset.completed || '[]');

    new Chart(trendsCanvas, {
      type: 'bar',
      data: {
        labels: months,
        datasets: [
          {
            label: 'New Projects',
            data: newData,
            backgroundColor: 'rgba(251,146,60,0.85)',
            borderRadius: 6,
            borderSkipped: false,
          },
          {
            label: 'Completed',
            data: compData,
            backgroundColor: 'rgba(129,140,248,0.85)',
            borderRadius: 6,
            borderSkipped: false,
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: {
            position: 'top',
            labels: { font: { size: 12 }, usePointStyle: true, pointStyleWidth: 10 }
          },
          tooltip: {
            backgroundColor: 'rgba(255,255,255,0.95)',
            titleColor: '#1f2937',
            bodyColor: '#1f2937',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 12,
            displayColors: true,
            callbacks: {
              label: ctx => ctx.dataset.label + ': ' + ctx.parsed.y
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { font: { size: 11 }, color: '#9ca3af' }
          },
          y: {
            beginAtZero: true,
            grid: { color: '#f1f5f9' },
            ticks: { precision: 0, font: { size: 11 }, color: '#9ca3af' }
          }
        }
      }
    });
  }


  // ═══════════════════════════════════════════════════════════════════
  // SECTION 6 — PROJECTS BY PROPERTY TYPE DOUGHNUT CHART
  // ═══════════════════════════════════════════════════════════════════
  const catCanvas = document.getElementById('categoryPerformanceChart');

  if (catCanvas) {
    const labels = JSON.parse(catCanvas.dataset.labels || '[]');
    const values = JSON.parse(catCanvas.dataset.values || '[]');
    const palette = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

    new Chart(catCanvas, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{
          data: values,
          backgroundColor: palette.slice(0, labels.length),
          borderWidth: 2,
          borderColor: '#fff',
          hoverBorderWidth: 3,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
          legend: {
            position: 'right',
            labels: { font: { size: 12 }, usePointStyle: true, padding: 16 }
          },
          tooltip: {
            backgroundColor: 'rgba(255,255,255,0.95)',
            titleColor: '#1f2937',
            bodyColor: '#1f2937',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 12,
            displayColors: true,
            callbacks: {
              label: function (context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const pct = ((context.parsed / total) * 100).toFixed(1);
                return context.label + ': ' + context.parsed + ' (' + pct + '%)';
              }
            }
          }
        },
        onHover: (event, activeElements) => {
          event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
        }
      }
    });
  }

});