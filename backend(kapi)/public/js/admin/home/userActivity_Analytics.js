// User Activity Analytics JavaScript

// User Growth Chart
const userGrowthCtx = document.getElementById('userGrowthChart');
if (userGrowthCtx) {
  new Chart(userGrowthCtx, {
    type: 'line',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      datasets: [{
        label: 'New Users',
        data: [120, 150, 180, 220, 290, 340, 380, 420, 460, 510, 580, 650],
        borderColor: '#8b5cf6',
        backgroundColor: 'rgba(139, 92, 246, 0.1)',
        borderWidth: 3,
        fill: true,
        tension: 0.4,
        pointRadius: 5,
        pointHoverRadius: 7,
        pointBackgroundColor: '#8b5cf6',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointHoverBackgroundColor: '#7c3aed',
        pointHoverBorderColor: '#fff',
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'top',
          labels: {
            font: {
              size: 12,
              weight: 'bold'
            },
            color: '#374151',
            usePointStyle: true,
            padding: 20
          }
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          padding: 12,
          titleFont: {
            size: 14,
            weight: 'bold'
          },
          bodyFont: {
            size: 13
          },
          borderColor: '#8b5cf6',
          borderWidth: 1,
          displayColors: false,
          callbacks: {
            label: function(context) {
              return 'Users: ' + context.parsed.y.toLocaleString();
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            font: {
              size: 11
            },
            color: '#6b7280',
            callback: function(value) {
              return value.toLocaleString();
            }
          },
          grid: {
            color: 'rgba(0, 0, 0, 0.05)',
            drawBorder: false
          }
        },
        x: {
          ticks: {
            font: {
              size: 11
            },
            color: '#6b7280'
          },
          grid: {
            display: false
          }
        }
      },
      interaction: {
        intersect: false,
        mode: 'index'
      }
    }
  });
}

// User Distribution Chart
const userDistributionCtx = document.getElementById('userDistributionChart');
if (userDistributionCtx) {
  new Chart(userDistributionCtx, {
    type: 'doughnut',
    data: {
      labels: ['Property Owners', 'Contractors', 'Inactive Users'],
      datasets: [{
        data: [1523, 1134, 190],
        backgroundColor: [
          '#10b981',
          '#f59e0b',
          '#ef4444'
        ],
        borderColor: '#ffffff',
        borderWidth: 4,
        hoverOffset: 15
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'bottom',
          labels: {
            font: {
              size: 12,
              weight: 'bold'
            },
            color: '#374151',
            padding: 20,
            usePointStyle: true,
            pointStyle: 'circle'
          }
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          padding: 12,
          titleFont: {
            size: 14,
            weight: 'bold'
          },
          bodyFont: {
            size: 13
          },
          borderColor: '#6366f1',
          borderWidth: 1,
          displayColors: true,
          callbacks: {
            label: function(context) {
              const label = context.label || '';
              const value = context.parsed || 0;
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = ((value / total) * 100).toFixed(1);
              return label + ': ' + value.toLocaleString() + ' (' + percentage + '%)';
            }
          }
        }
      },
      cutout: '65%',
      animation: {
        animateScale: true,
        animateRotate: true
      }
    }
  });
}

// Add hover effects to stat cards
document.querySelectorAll('.group').forEach(card => {
  card.addEventListener('mouseenter', function() {
    this.style.transform = 'translateY(-8px)';
  });
  
  card.addEventListener('mouseleave', function() {
    this.style.transform = 'translateY(0)';
  });
});

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
