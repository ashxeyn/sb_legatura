document.addEventListener('DOMContentLoaded', function() {
  // Stat cards interactive effects
  const statCards = document.querySelectorAll('.stat-card');
  
  statCards.forEach(card => {
    // Add ripple effect on click
    card.addEventListener('click', function(e) {
      const ripple = document.createElement('span');
      const rect = card.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;
      
      ripple.style.width = ripple.style.height = size + 'px';
      ripple.style.left = x + 'px';
      ripple.style.top = y + 'px';
      ripple.classList.add('ripple');
      
      card.appendChild(ripple);
      
      setTimeout(() => {
        ripple.remove();
      }, 600);
    });

    // Animate progress bars on scroll into view
    const progressBar = card.querySelector('.stat-progress-fill');
    if (progressBar) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const width = progressBar.style.width;
            progressBar.style.width = '0%';
            setTimeout(() => {
              progressBar.style.width = width;
            }, 100);
          }
        });
      }, { threshold: 0.5 });

      observer.observe(card);
    }

    // Add tooltip functionality
    card.addEventListener('mouseenter', function() {
      const description = card.querySelector('.stat-description');
      if (description) {
        description.style.transition = 'all 0.3s ease';
      }
    });
  });

  // Animate stat values counting up
  const statValues = document.querySelectorAll('.stat-value');
  
  statValues.forEach(valueElement => {
    const text = valueElement.textContent.trim();
    
    // Check if it's a number (not currency)
    if (!text.includes('₱') && !text.includes(',')) {
      const finalValue = parseInt(text);
      if (!isNaN(finalValue)) {
        animateValue(valueElement, 0, finalValue, 1000);
      }
    } else if (text.includes('₱')) {
      // Animate currency values
      const numericValue = parseFloat(text.replace('₱', '').replace(/,/g, ''));
      if (!isNaN(numericValue)) {
        animateCurrency(valueElement, 0, numericValue, 1000);
      }
    }
  });

  function animateValue(element, start, end, duration) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;

    const timer = setInterval(() => {
      current += increment;
      if (current >= end) {
        element.textContent = Math.round(end);
        clearInterval(timer);
      } else {
        element.textContent = Math.round(current);
      }
    }, 16);
  }

  function animateCurrency(element, start, end, duration) {
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;

    const timer = setInterval(() => {
      current += increment;
      if (current >= end) {
        element.textContent = '₱' + end.toLocaleString('en-US', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });
        clearInterval(timer);
      } else {
        element.textContent = '₱' + current.toLocaleString('en-US', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });
      }
    }, 16);
  }

  // Add pulse animation to expiring soon card if count > 0
  const expiringCard = document.querySelector('.stat-card-orange');
  if (expiringCard) {
    const expiringValue = expiringCard.querySelector('.stat-value');
    const count = parseInt(expiringValue.textContent);
    
    if (count > 0) {
      setInterval(() => {
        expiringCard.style.transform = 'scale(1.02)';
        setTimeout(() => {
          expiringCard.style.transform = 'scale(1)';
        }, 200);
      }, 3000);
    }
  }

  // Card click actions (optional - can be used to show detailed info)
  statCards.forEach((card, index) => {
    card.addEventListener('click', function() {
      console.log('Card clicked:', card.querySelector('.stat-label').textContent);
      // You can add modal or detailed view here
    });
  });

  // Bar Chart Animation
  const barItems = document.querySelectorAll('.bar-item');
  const barFills = document.querySelectorAll('.bar-fill');

  // Animate bars on scroll into view
  const chartObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateBars();
        chartObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.3 });

  const chartCard = document.querySelector('.subscription-chart-card');
  if (chartCard) {
    chartObserver.observe(chartCard);
  }

  function animateBars() {
    barFills.forEach((bar, index) => {
      const count = parseFloat(bar.getAttribute('data-count'));
      const maxCount = parseFloat(bar.getAttribute('data-max'));
      const percentage = maxCount > 0 ? (count / maxCount) * 100 : 0;
      
      setTimeout(() => {
        bar.style.height = percentage + '%';
      }, index * 200);
    });
  }

  // Bar item interactions
  barItems.forEach((item, index) => {
    // Add click ripple effect
    item.addEventListener('click', function(e) {
      const ripple = document.createElement('span');
      ripple.classList.add('bar-ripple');
      
      item.appendChild(ripple);
      
      setTimeout(() => {
        ripple.remove();
      }, 600);

      // Log tier information
      const tierName = item.querySelector('.bar-label').textContent;
      const count = item.querySelector('.bar-value').textContent;
      console.log(`${tierName}: ${count} subscriptions`);
    });

    // Add hover effect to legend
    item.addEventListener('mouseenter', function() {
      const legendItems = document.querySelectorAll('.legend-item-inline');
      if (legendItems[index]) {
        legendItems[index].style.transform = 'scale(1.1)';
        legendItems[index].style.transition = 'transform 0.3s ease';
      }
    });

    item.addEventListener('mouseleave', function() {
      const legendItems = document.querySelectorAll('.legend-item-inline');
      if (legendItems[index]) {
        legendItems[index].style.transform = 'scale(1)';
      }
    });
  });

  // Add tooltip on bar hover
  barFills.forEach((bar) => {
    bar.addEventListener('mouseenter', function() {
      const value = bar.querySelector('.bar-value');
      if (value) {
        value.style.opacity = '1';
      }
    });
  });

  // ---------------- Revenue Chart ----------------
  const revenueDataEl = document.getElementById('initialRevenueData');
  let revenueData = {};
  try { revenueData = JSON.parse(revenueDataEl ? revenueDataEl.textContent : '{}'); } catch(e){ console.error('Revenue data parse error', e); }

  const revenueCanvas = document.getElementById('subscriptionRevenueChart');
  const loadingEl = document.getElementById('revenueLoading');
  const tierSelect = document.getElementById('revenueTierSelect');
  let revenueChart = null;

  if (revenueCanvas && revenueData.months) {
    const ctx = revenueCanvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0,0,0,revenueCanvas.height);
    gradient.addColorStop(0,'rgba(59,130,246,0.45)');
    gradient.addColorStop(1,'rgba(59,130,246,0)');

    revenueChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: revenueData.months,
        datasets: [
          {
            label: (revenueData.currentYear || 'This Year') + ' Revenue',
            data: revenueData.currentYearData || [],
            borderColor: '#3b82f6',
            backgroundColor: gradient,
            fill: true,
            tension: 0.35,
            pointRadius: 4,
            pointHoverRadius: 6,
            borderWidth: 2
          },
          {
            label: (revenueData.previousYear || 'Prev Year') + ' Revenue',
            data: revenueData.previousYearData || [],
            borderColor: '#9ca3af',
            backgroundColor: 'transparent',
            fill: false,
            tension: 0.35,
            pointRadius: 0,
            borderDash: [6,4],
            borderWidth: 2
          }
        ]
      },
      options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        maintainAspectRatio: false,
        scales: {
          y: {
            ticks: {
              callback: val => '₱' + Number(val).toLocaleString()
            },
            grid: { color: 'rgba(0,0,0,0.05)' }
          },
          x: { grid: { display: false } }
        },
        plugins: {
          legend: { position: 'bottom' },
          tooltip: {
            callbacks: {
              label: ctx => {
                const v = ctx.raw || 0;
                return ctx.dataset.label + ': ₱' + Number(v).toLocaleString(undefined,{ minimumFractionDigits: 2, maximumFractionDigits: 2 });
              }
            }
          }
        }
      }
    });
  }

  async function fetchRevenue(tier){
    if(!revenueChart) return;
    try {
      loadingEl.removeAttribute('hidden');
      const url = tier === 'all' ? '/admin/analytics/subscription/revenue' : `/admin/analytics/subscription/revenue?tier=${tier}`;
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if(!res.ok) throw new Error('Network error');
      const data = await res.json();
      revenueChart.data.labels = data.months;
      revenueChart.data.datasets[0].data = data.currentYearData;
      revenueChart.data.datasets[0].label = data.currentYear + ' Revenue';
      revenueChart.data.datasets[1].data = data.previousYearData;
      revenueChart.data.datasets[1].label = data.previousYear + ' Revenue';
      revenueChart.update();
    } catch(err){
      console.error('Fetch revenue failed', err);
    } finally {
      loadingEl.setAttribute('hidden','');
    }
  }

  if(tierSelect){
    tierSelect.addEventListener('change', () => fetchRevenue(tierSelect.value));
  }
});

// Add ripple effect styles dynamically
const style = document.createElement('style');
style.textContent = `
  .stat-card {
    position: relative;
    overflow: hidden;
  }

  .ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.6);
    transform: scale(0);
    animation: ripple-animation 0.6s ease-out;
    pointer-events: none;
  }

  @keyframes ripple-animation {
    to {
      transform: scale(4);
      opacity: 0;
    }
  }

  .stat-card:active .stat-icon {
    transform: scale(0.95) rotate(5deg);
  }

  .bar-ripple {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.7);
    transform: translate(-50%, -50%) scale(0);
    animation: bar-ripple-animation 0.6s ease-out;
    pointer-events: none;
  }

  @keyframes bar-ripple-animation {
    to {
      transform: translate(-50%, -50%) scale(6);
      opacity: 0;
    }
  }

  .bar-item {
    position: relative;
    overflow: visible;
  }
`;
document.head.appendChild(style);
