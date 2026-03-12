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
