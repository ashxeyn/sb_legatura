// Reports and Analytics JavaScript

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

// Initialize animations on page load
document.addEventListener('DOMContentLoaded', function() {
  // Animate all stat numbers
  const statNumbers = document.querySelectorAll('.stat-number');
  statNumbers.forEach(element => {
    animateNumber(element);
  });

  // Export button functionality
  const exportButtons = document.querySelectorAll('.export-btn');
  exportButtons.forEach(button => {
    button.addEventListener('click', function() {
      const type = this.getAttribute('data-type');
      const originalText = this.innerHTML;
      
      // Get checked options
      const card = this.closest('.border-2');
      const checkedOptions = card.querySelectorAll('input[type="checkbox"]:checked');
      const selectedOptions = Array.from(checkedOptions).map(cb => cb.nextElementSibling.textContent);
      
      // Show loading state
      this.innerHTML = `
        <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Generating...</span>
      `;
      this.disabled = true;
      
      // Simulate export process
      setTimeout(() => {
        this.innerHTML = originalText;
        this.disabled = false;
        
        // Show success message with selected options
        const optionsText = selectedOptions.length > 0 ? `\nIncluding: ${selectedOptions.join(', ')}` : '';
        alert(`${type.toUpperCase()} report generated successfully!${optionsText}\n\nDownload will start shortly.`);
        
        // Add to history (in a real application, this would update the database)
        console.log(`Generated ${type.toUpperCase()} report with options:`, selectedOptions);
      }, 2000);
    });
  });

  // Checkbox interaction - visual feedback
  const checkboxes = document.querySelectorAll('input[type="checkbox"]');
  checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
      const label = this.closest('label');
      if (this.checked) {
        label.style.transform = 'scale(1.02)';
        setTimeout(() => {
          label.style.transform = 'scale(1)';
        }, 200);
      }
    });
  });

  // Period selector change
  const periodSelector = document.querySelector('select');
  if (periodSelector) {
    periodSelector.addEventListener('change', function() {
      const selectedPeriod = this.value;
      
      // Visual feedback
      this.style.transform = 'scale(0.98)';
      setTimeout(() => {
        this.style.transform = 'scale(1)';
      }, 100);
      
      console.log(`Period changed to: ${selectedPeriod}`);
      // In a real application, this would filter the data based on the selected period
    });
  }

  // Download button functionality in history table
  const downloadButtons = document.querySelectorAll('tbody button');
  downloadButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.stopPropagation();
      
      const row = this.closest('tr');
      const reportName = row.querySelector('.font-semibold').textContent;
      const reportType = row.querySelector('.inline-flex').textContent.trim();
      
      // Visual feedback
      this.style.transform = 'scale(0.95)';
      setTimeout(() => {
        this.style.transform = 'scale(1)';
      }, 100);
      
      // Show download progress
      const originalText = this.innerHTML;
      this.innerHTML = `
        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
      `;
      
      setTimeout(() => {
        this.innerHTML = originalText;
        console.log(`Downloading: ${reportName} (${reportType})`);
      }, 1500);
    });
  });

  // Table row hover effect
  const tableRows = document.querySelectorAll('tbody tr');
  tableRows.forEach(row => {
    row.addEventListener('mouseenter', function() {
      this.style.transform = 'translateX(4px)';
    });
    
    row.addEventListener('mouseleave', function() {
      this.style.transform = 'translateX(0)';
    });
  });

  // Filter button functionality
  const filterButton = document.querySelector('.bg-white.text-gray-800');
  if (filterButton) {
    filterButton.addEventListener('click', function() {
      // Visual feedback
      this.style.transform = 'scale(0.95)';
      setTimeout(() => {
        this.style.transform = 'scale(1)';
      }, 100);
      
      alert('Filter options:\n\n• Report Type (PDF, Excel, CSV)\n• Date Range\n• Generated By\n• File Size\n\nFilter functionality coming soon!');
    });
  }

  // Card hover animations
  const reportCards = document.querySelectorAll('.border-2.rounded-2xl');
  reportCards.forEach(card => {
    card.addEventListener('mouseenter', function() {
      const icon = this.querySelector('.bg-opacity-20, .bg-red-100, .bg-green-100, .bg-blue-100');
      if (icon) {
        icon.style.transform = 'rotate(10deg) scale(1.1)';
      }
    });
    
    card.addEventListener('mouseleave', function() {
      const icon = this.querySelector('.bg-opacity-20, .bg-red-100, .bg-green-100, .bg-blue-100');
      if (icon) {
        icon.style.transform = 'rotate(0deg) scale(1)';
      }
    });
  });

  // Add transition to all elements that need it
  const transitionElements = document.querySelectorAll('label, button, select, tr');
  transitionElements.forEach(el => {
    el.style.transition = 'all 0.2s ease';
  });

  // Detailed Report Configuration Export Buttons
  const detailedExportButtons = document.querySelectorAll('.detailed-export-btn');
  detailedExportButtons.forEach(button => {
    button.addEventListener('click', function() {
      const type = this.getAttribute('data-type');
      const originalText = this.innerHTML;
      
      // Get filter values
      const dateInputs = document.querySelectorAll('input[type="date"]');
      const userTypeSelect = document.querySelector('select[class*="User Type"]');
      const locationSelect = document.querySelectorAll('select')[2]; // Location dropdown
      const statusSelect = document.querySelectorAll('select')[3]; // Project Status dropdown
      
      // Get checked metrics
      const checkedMetrics = Array.from(document.querySelectorAll('.space-y-3 input[type="checkbox"]:checked'))
        .map(cb => cb.nextElementSibling.querySelector('span').textContent);
      
      // Show loading state
      this.innerHTML = `
        <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Generating...</span>
      `;
      this.disabled = true;
      
      // Simulate export process
      setTimeout(() => {
        this.innerHTML = originalText;
        this.disabled = false;
        
        // Build report summary
        let summary = `Detailed ${type.toUpperCase()} report generated successfully!\n\n`;
        summary += `Filters Applied:\n`;
        if (dateInputs[0].value || dateInputs[1].value) {
          summary += `• Date Range: ${dateInputs[0].value || 'Start'} to ${dateInputs[1].value || 'End'}\n`;
        }
        if (checkedMetrics.length > 0) {
          summary += `\nMetrics Included:\n${checkedMetrics.map(m => `• ${m}`).join('\n')}\n`;
        }
        summary += `\nDownload will start shortly.`;
        
        alert(summary);
        console.log(`Generated detailed ${type.toUpperCase()} report with filters and metrics`);
      }, 2500);
    });
  });

  // Date input interactions
  const dateInputs = document.querySelectorAll('input[type="date"]');
  dateInputs.forEach(input => {
    input.addEventListener('change', function() {
      this.style.transform = 'scale(0.98)';
      setTimeout(() => {
        this.style.transform = 'scale(1)';
      }, 100);
    });
  });

  // Select dropdown interactions
  const selects = document.querySelectorAll('select');
  selects.forEach(select => {
    select.addEventListener('focus', function() {
      this.style.boxShadow = '0 0 0 3px rgba(99, 102, 241, 0.1)';
    });
    
    select.addEventListener('blur', function() {
      this.style.boxShadow = 'none';
    });
  });

  // Enhanced checkbox interactions in detailed config
  const detailedCheckboxes = document.querySelectorAll('.space-y-3 input[type="checkbox"]');
  detailedCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
      const label = this.closest('label');
      if (this.checked) {
        label.style.backgroundColor = 'rgba(99, 102, 241, 0.05)';
        label.style.borderColor = 'rgb(99, 102, 241)';
        label.style.transform = 'scale(1.02)';
        setTimeout(() => {
          label.style.transform = 'scale(1)';
        }, 200);
      } else {
        label.style.backgroundColor = '';
        label.style.borderColor = '';
      }
    });
  });

  console.log('Reports and Analytics page initialized');
});
