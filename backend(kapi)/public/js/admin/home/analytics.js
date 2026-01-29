document.addEventListener('DOMContentLoaded', function() {
  // Initialize Projects Donut Chart
  const projectsCtx = document.getElementById('projectsDonutChart');
  
  if (projectsCtx) {
    const labels = JSON.parse(projectsCtx.dataset.labels || '[]');
    const values = JSON.parse(projectsCtx.dataset.values || '[]');
    
    const colors = [
      '#1f2937', // Completed - Dark Gray/Black
      '#60a5fa', // Ongoing - Blue
      '#34d399', // On Hold - Green  
      '#a78bfa'  // Cancelled - Purple/Lavender
    ];
    
    const hoverColors = [
      '#374151', // Completed hover
      '#3b82f6', // Ongoing hover
      '#10b981', // On Hold hover
      '#8b5cf6'  // Cancelled hover
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
          legend: {
            display: false
          },
          tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
            titleColor: '#1f2937',
            bodyColor: '#1f2937',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 12,
            displayColors: true,
            callbacks: {
              label: function(context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((context.parsed / total) * 100).toFixed(1);
                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
              }
            }
          }
        },
        onHover: (event, activeElements) => {
          event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
        }
      }
    });

    // Make legend items interactive
    const legendItems = document.querySelectorAll('.legend-item');
    
    legendItems.forEach((item, index) => {
      item.addEventListener('click', function() {
        // Toggle dataset visibility
        const meta = projectsChart.getDatasetMeta(0);
        meta.data[index].hidden = !meta.data[index].hidden;
        projectsChart.update();
        
        // Toggle active class
        this.classList.toggle('active');
      });
      
      // Highlight on hover
      item.addEventListener('mouseenter', function() {
        projectsChart.setActiveElements([{
          datasetIndex: 0,
          index: index
        }]);
        projectsChart.update();
      });
      
      item.addEventListener('mouseleave', function() {
        projectsChart.setActiveElements([]);
        projectsChart.update();
      });
    });
  }

  // Initialize Project Success Rate Pie Chart
  const successRateCtx = document.getElementById('projectSuccessRateChart');
  
  if (successRateCtx) {
    const labels = JSON.parse(successRateCtx.dataset.labels || '[]');
    const values = JSON.parse(successRateCtx.dataset.values || '[]');
    const colors = JSON.parse(successRateCtx.dataset.colors || '[]');
    
    // Generate hover colors (slightly darker)
    const hoverColors = colors.map(color => {
      // Convert hex to RGB and darken
      const r = parseInt(color.slice(1, 3), 16);
      const g = parseInt(color.slice(3, 5), 16);
      const b = parseInt(color.slice(5, 7), 16);
      
      const darkenFactor = 0.85;
      const newR = Math.floor(r * darkenFactor);
      const newG = Math.floor(g * darkenFactor);
      const newB = Math.floor(b * darkenFactor);
      
      return `#${newR.toString(16).padStart(2, '0')}${newG.toString(16).padStart(2, '0')}${newB.toString(16).padStart(2, '0')}`;
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
          legend: {
            display: false
          },
          tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
            titleColor: '#1f2937',
            bodyColor: '#1f2937',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 12,
            displayColors: true,
            callbacks: {
              label: function(context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((context.parsed / total) * 100).toFixed(1);
                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
              }
            }
          }
        },
        onHover: (event, activeElements) => {
          event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
        }
      }
    });

    // Make success rate legend items interactive
    const successLegendItems = document.querySelectorAll('.success-rate-legend-item');
    
    successLegendItems.forEach((item, index) => {
      // Set arrow color to match the line color
      const line = item.querySelector('.success-rate-legend-line');
      const lineColor = line.style.background;
      line.style.setProperty('color', lineColor, 'important');
      
      item.addEventListener('click', function() {
        // Toggle dataset visibility
        const meta = successRateChart.getDatasetMeta(0);
        meta.data[index].hidden = !meta.data[index].hidden;
        successRateChart.update();
        
        // Toggle active class
        this.classList.toggle('active');
      });
      
      // Highlight on hover
      item.addEventListener('mouseenter', function() {
        successRateChart.setActiveElements([{
          datasetIndex: 0,
          index: index
        }]);
        successRateChart.update();
      });
      
      item.addEventListener('mouseleave', function() {
        successRateChart.setActiveElements([]);
        successRateChart.update();
      });
    });
  }

  // Initialize Projects Timeline Chart
  const timelineCtx = document.getElementById('projectsTimelineChart');
  let projectsTimelineChart = null;
  
  if (timelineCtx) {
    const months = JSON.parse(timelineCtx.dataset.months || '[]');
    const newProjects = JSON.parse(timelineCtx.dataset.new || '[]');
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
            backgroundColor: 'rgba(251, 146, 60, 0.1)',
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
            backgroundColor: 'rgba(129, 140, 248, 0.1)',
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
        interaction: {
          mode: 'index',
          intersect: false,
        },
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
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
              title: function(context) {
                return context[0].label;
              },
              label: function(context) {
                return context.dataset.label + ': ' + context.parsed.y + ' Projects';
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
              color: '#f3f4f6',
              drawBorder: false,
            },
            ticks: {
              color: '#9ca3af',
              font: {
                size: 12
              },
              stepSize: 2
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
              color: '#9ca3af',
              font: {
                size: 12
              }
            }
          },
        },
      },
    });

    // Timeline Dropdown Toggle
    const timelineDropdownBtn = document.getElementById('timelineDropdownBtn');
    const timelineDropdownMenu = document.getElementById('timelineDropdownMenu');
    
    if (timelineDropdownBtn && timelineDropdownMenu) {
      timelineDropdownBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        timelineDropdownMenu.classList.toggle('active');
        timelineDropdownBtn.classList.toggle('active');
      });

      // Close dropdown when clicking outside
      document.addEventListener('click', function(e) {
        if (!timelineDropdownMenu.contains(e.target) && !timelineDropdownBtn.contains(e.target)) {
          timelineDropdownMenu.classList.remove('active');
          timelineDropdownBtn.classList.remove('active');
        }
      });

      // Handle dropdown item selection
      const timelineDropdownItems = timelineDropdownMenu.querySelectorAll('.timeline-dropdown-item');
      timelineDropdownItems.forEach(item => {
        item.addEventListener('click', function() {
          // Remove active class from all items
          timelineDropdownItems.forEach(i => i.classList.remove('active'));
          // Add active class to clicked item
          this.classList.add('active');
          
          const range = this.dataset.range;
          const rangeText = this.textContent;
          
          // Update displayed date range
          document.querySelector('.timeline-date-range').textContent = rangeText;
          
          // Close dropdown
          timelineDropdownMenu.classList.remove('active');
          timelineDropdownBtn.classList.remove('active');
          
          // Fetch new data and update chart
          fetchTimelineData(range);
        });
      });
    }

    // Make legend items interactive
    const timelineLegendItems = document.querySelectorAll('.timeline-legend-item');
    
    timelineLegendItems.forEach((item, index) => {
      item.addEventListener('click', function() {
        const datasetIndex = parseInt(this.dataset.dataset);
        const meta = projectsTimelineChart.getDatasetMeta(datasetIndex);
        
        // Toggle dataset visibility
        meta.hidden = meta.hidden === null ? true : !meta.hidden;
        projectsTimelineChart.update();
        
        // Toggle hidden class
        this.classList.toggle('hidden');
      });
    });
  }

  // Function to fetch timeline data for selected range
  function fetchTimelineData(range) {
    // Show loading state
    const timelineContainer = document.querySelector('.timeline-chart-container');
    if (timelineContainer) {
      timelineContainer.style.opacity = '0.5';
    }

    // Fetch data via AJAX
    fetch(`/admin/analytics/timeline?range=${range}`)
      .then(response => response.json())
      .then(data => {
        // Update chart
        if (projectsTimelineChart) {
          projectsTimelineChart.data.labels = data.months;
          projectsTimelineChart.data.datasets[0].data = data.newProjects;
          projectsTimelineChart.data.datasets[1].data = data.completedProjects;
          projectsTimelineChart.update();
        }
        
        // Update date range display
        document.querySelector('.timeline-date-range').textContent = data.dateRange;
        
        // Remove loading state
        if (timelineContainer) {
          timelineContainer.style.opacity = '1';
        }
      })
      .catch(error => {
        console.error('Error fetching timeline data:', error);
        if (timelineContainer) {
          timelineContainer.style.opacity = '1';
        }
      });
  }
});
