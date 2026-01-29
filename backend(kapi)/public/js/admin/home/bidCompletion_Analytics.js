// Bid Completion Analytics JavaScript

// Number counter animation
function animateNumber(element) {
  const target = parseFloat(element.getAttribute('data-target'));
  const duration = 1500;
  const start = 0;
  const startTime = performance.now();

  function update(currentTime) {
    const elapsed = currentTime - startTime;
    const progress = Math.min(elapsed / duration, 1);
    
    // Easing function
    const easeOutQuad = progress => 1 - (1 - progress) * (1 - progress);
    const current = start + (target - start) * easeOutQuad(progress);
    
    // Format number with decimals if needed
    if (target % 1 !== 0) {
      element.textContent = current.toFixed(1);
    } else {
      element.textContent = Math.floor(current);
    }
    
    if (progress < 1) {
      requestAnimationFrame(update);
    }
  }
  
  requestAnimationFrame(update);
}

// Animate progress bars
function animateProgressBars() {
  const progressBars = document.querySelectorAll('.progress-bar');
  progressBars.forEach((bar, index) => {
    const targetWidth = bar.getAttribute('data-width');
    setTimeout(() => {
      bar.style.width = targetWidth + '%';
    }, 500 + (index * 200));
  });
}

// Initialize number animations on page load
document.addEventListener('DOMContentLoaded', function() {
  // Animate all stat numbers
  const statNumbers = document.querySelectorAll('.stat-number');
  statNumbers.forEach(element => {
    animateNumber(element);
  });

  // Animate progress bars
  animateProgressBars();

  // Bid Timeline Analysis Chart
  const timelineCtx = document.getElementById('bidTimelineChart');
  if (timelineCtx) {
    new Chart(timelineCtx, {
      type: 'bar',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [
          {
            label: 'Bids Submitted',
            data: [45, 52, 48, 65, 72, 68, 85, 78, 82, 90, 85, 95],
            backgroundColor: 'rgba(168, 85, 247, 0.8)',
            borderColor: 'rgb(168, 85, 247)',
            borderWidth: 2,
            borderRadius: 8,
            hoverBackgroundColor: 'rgba(168, 85, 247, 1)'
          },
          {
            label: 'Bids Completed',
            data: [38, 45, 42, 58, 63, 60, 74, 68, 72, 79, 75, 83],
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            borderColor: 'rgb(16, 185, 129)',
            borderWidth: 2,
            borderRadius: 8,
            hoverBackgroundColor: 'rgba(16, 185, 129, 1)'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 2,
        plugins: {
          legend: {
            display: true,
            position: 'top',
            labels: {
              usePointStyle: true,
              padding: 20,
              font: {
                size: 13,
                weight: '600'
              }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            cornerRadius: 8,
            titleFont: {
              size: 14,
              weight: 'bold'
            },
            bodyFont: {
              size: 13
            },
            displayColors: true,
            callbacks: {
              label: function(context) {
                return context.dataset.label + ': ' + context.parsed.y + ' bids';
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)',
              drawBorder: false
            },
            ticks: {
              font: {
                size: 12
              },
              padding: 10
            }
          },
          x: {
            grid: {
              display: false,
              drawBorder: false
            },
            ticks: {
              font: {
                size: 12
              },
              padding: 10
            }
          }
        },
        interaction: {
          mode: 'index',
          intersect: false
        }
      }
    });
  }

  // Bid Status Distribution Chart
  const statusCtx = document.getElementById('bidStatusChart');
  if (statusCtx) {
    new Chart(statusCtx, {
      type: 'doughnut',
      data: {
        labels: ['Accepted', 'Pending', 'Under Review', 'Rejected', 'Withdrawn'],
        datasets: [{
          data: [387, 156, 98, 67, 45],
          backgroundColor: [
            'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(156, 163, 175, 0.8)'
          ],
          borderColor: [
            'rgb(16, 185, 129)',
            'rgb(245, 158, 11)',
            'rgb(59, 130, 246)',
            'rgb(239, 68, 68)',
            'rgb(156, 163, 175)'
          ],
          borderWidth: 3,
          hoverOffset: 15,
          hoverBorderWidth: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 1.5,
        plugins: {
          legend: {
            display: true,
            position: 'right',
            labels: {
              usePointStyle: true,
              padding: 20,
              font: {
                size: 13,
                weight: '600'
              },
              generateLabels: function(chart) {
                const data = chart.data;
                if (data.labels.length && data.datasets.length) {
                  return data.labels.map((label, i) => {
                    const value = data.datasets[0].data[i];
                    const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                    const percentage = ((value / total) * 100).toFixed(1);
                    return {
                      text: `${label}: ${percentage}%`,
                      fillStyle: data.datasets[0].backgroundColor[i],
                      hidden: false,
                      index: i
                    };
                  });
                }
                return [];
              }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            cornerRadius: 8,
            titleFont: {
              size: 14,
              weight: 'bold'
            },
            bodyFont: {
              size: 13
            },
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.parsed;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((value / total) * 100).toFixed(1);
                return `${label}: ${value} bids (${percentage}%)`;
              }
            }
          }
        },
        cutout: '65%',
        animation: {
          animateRotate: true,
          animateScale: true,
          duration: 1500,
          easing: 'easeOutQuart'
        }
      }
    });
  }

  // Geographic Distribution Chart
  const geoCtx = document.getElementById('geographicDistributionChart');
  if (geoCtx) {
    new Chart(geoCtx, {
      type: 'bar',
      data: {
        labels: ['Tetuan', 'Tumaga', 'Sinunuc', 'Malagutay', 'Baliwasan', 'Upper Calarian', 'Others'],
        datasets: [{
          label: 'Active Projects',
          data: [130, 210, 170, 90, 195, 120, 185],
          backgroundColor: 'rgba(59, 130, 246, 0.8)',
          borderColor: 'rgb(59, 130, 246)',
          borderWidth: 2,
          borderRadius: 8,
          hoverBackgroundColor: 'rgba(59, 130, 246, 1)'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: 2.5,
        plugins: {
          legend: {
            display: true,
            position: 'bottom',
            labels: {
              usePointStyle: true,
              padding: 20,
              font: {
                size: 13,
                weight: '600'
              }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            cornerRadius: 8,
            titleFont: {
              size: 14,
              weight: 'bold'
            },
            bodyFont: {
              size: 13
            },
            displayColors: true,
            callbacks: {
              label: function(context) {
                return 'Active Projects: ' + context.parsed.y;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(0, 0, 0, 0.05)',
              drawBorder: false
            },
            ticks: {
              font: {
                size: 12
              },
              padding: 10
            },
            title: {
              display: true,
              text: 'Number of Projects',
              font: {
                size: 13,
                weight: '600'
              }
            }
          },
          x: {
            grid: {
              display: false,
              drawBorder: false
            },
            ticks: {
              font: {
                size: 12
              },
              padding: 10
            }
          }
        }
      }
    });
  }

  // Table row click effect
  const tableRows = document.querySelectorAll('tbody tr');
  tableRows.forEach(row => {
    row.addEventListener('click', function() {
      this.style.transform = 'scale(0.98)';
      setTimeout(() => {
        this.style.transform = 'scale(1)';
      }, 100);
    });
  });

  // Export button functionality
  const exportButton = document.querySelector('.bg-gradient-to-r.from-violet-500 button');
  if (exportButton) {
    exportButton.addEventListener('click', function() {
      const originalText = this.innerHTML;
      
      // Show loading state
      this.innerHTML = `
        <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Exporting...
      `;
      this.disabled = true;
      
      // Simulate export process
      setTimeout(() => {
        this.innerHTML = originalText;
        this.disabled = false;
        alert('Bid data exported successfully!');
      }, 1500);
    });
  }
});
