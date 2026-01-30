document.addEventListener('DOMContentLoaded', function() {

  // Fetch and update table content (similar to propertyOwner module)
  async function fetchAndUpdate(url) {
    try {
      const response = await fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) throw new Error('Network response was not ok');

      const data = await response.json();

      const projectsWrap = document.getElementById('projectsTableWrap');
      if (projectsWrap && data.html) {
        projectsWrap.innerHTML = data.html;
      }

      // Re-attach action listeners after updating the table
      if (typeof window.attachActionListeners === 'function') {
        window.attachActionListeners();
      }

    } catch (error) {
      console.error('Error fetching data:', error);
    }
  }

  // Make fetchAndUpdate globally accessible
  window.refreshProjectsTable = function() {
    const url = window.location.href;
    fetchAndUpdate(url);
  };

  // Make attachActionListeners globally accessible so filters.js can call it after AJAX updates
  window.attachActionListeners = function() {
    const viewButtons = document.querySelectorAll('.view-btn');
    const editButtons = document.querySelectorAll('.edit-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');

    viewButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        addRipple(this, e);
        const id = this.getAttribute('data-id');
        if (id) {
          setTimeout(() => {
            fetchProjectDetails(id);
          }, 200);
        }
      });
    });

    editButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        addRipple(this, e);
        const id = this.getAttribute('data-id');
        if (id) {
          setTimeout(() => {
            window.location.href = `/admin/project-management/${id}/edit`;
          }, 200);
        }
      });
    });

    deleteButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        addRipple(this, e);
        const id = this.getAttribute('data-id');
        if (id) {
          setTimeout(() => {
            if (confirm('Are you sure you want to delete this project?')) {
              showNotification('Project deleted successfully', 'success');
            }
          }, 200);
        }
      });
    });

    // Add row click handlers for highlight
    const tableRows = document.querySelectorAll('#projectsTableBody tr');
    tableRows.forEach(row => {
      row.addEventListener('click', function() {
        tableRows.forEach(r => r.classList.remove('bg-orange-50'));
        this.classList.add('bg-orange-50');
      });
    });
  };

  // Initial attachment of action listeners
  if (typeof window.attachActionListeners === 'function') {
    window.attachActionListeners();
  }

  // Fetch project details and open appropriate modal
  async function fetchProjectDetails(projectId) {
    try {
      const response = await fetch(`/admin/project-management/${projectId}/details`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      if (!response.ok) throw new Error('Failed to fetch project details');

      const result = await response.json();

      if (result.success && result.data) {
        const data = result.data;

        // Determine which modal to open based on project status
        switch(data.projectStatus) {
          case 'open':
          case 'bidding_closed':
            showOpenProjectModal(projectId);
            break;
          case 'in_progress':
            showOngoingProjectModal(projectId);
            break;
          case 'completed':
            openCompletedProjectModal(data);
            break;
          case 'halt':
            openHaltedProjectModal(data);
            break;
          case 'terminated':
          case 'cancelled':
            openCancelledProjectModal(data);
            break;
          default:
            openBiddingDetailsModal(data);
        }
      } else {
        showNotification('Failed to load project details', 'error');
      }
    } catch (error) {
      console.error('Error fetching project details:', error);
      showNotification('An error occurred while loading project details', 'error');
    }
  }

  // Open Bidding Details Modal
  function openBiddingDetailsModal(data) {
    const modal = document.getElementById('biddingDetailsModal');
    if (!modal) return;

    // Populate basic info
    setTextContent('biddingProjectTitle', data.title);
    setTextContent('biddingProjectId', data.projectId);
    setTextContent('biddingOwnerName', data.ownerName);
    setTextContent('biddingPropertyType', data.propertyType);
    setTextContent('biddingLotSize', data.lotSize ? `${data.lotSize} sqm` : '—');
    setTextContent('biddingTimeline', data.timelineDisplay || '—');
    setTextContent('biddingBudget', formatBudget(data.budgetMin, data.budgetMax));
    setTextContent('biddingDeadline', formatDate(data.biddingDue));
    setTextContent('biddingDescription', data.description);

    // Show modal
    showModal(modal);
  }

  // Open Ongoing Project Modal
  function openOngoingProjectModal(data) {
    const modal = document.getElementById('ongoingProjectModal');
    if (!modal) return;

    // Populate header
    setTextContent('ongoingProjectTitle', data.title);
    setTextContent('ongoingProjectId', data.projectId);
    setTextContent('ongoingOwnerName', data.ownerName);

    // Populate project details
    setTextContent('ongoingPropertyType', data.propertyType);
    setTextContent('ongoingPropertyAddress', data.propertyAddress);
    setTextContent('ongoingLotSize', data.lotSize ? `${data.lotSize} sqm` : '—');
    setTextContent('ongoingFloorArea', data.floorArea ? `${data.floorArea} sqm` : '—');
    setTextContent('ongoingBudget', formatBudget(data.budgetMin, data.budgetMax));
    setTextContent('ongoingTimeline', data.timelineDisplay || '—');
    setTextContent('ongoingDescription', data.description);

    // Populate contractor details
    setTextContent('ongoingContractorName', data.contractorName);
    setTextContent('ongoingContractorRepName', data.contractorRepName || '—');
    setTextContent('ongoingContractorEmail', data.contractorEmail || '—');
    setTextContent('ongoingContractorPhone', data.contractorPhone || '—');
    setTextContent('ongoingContractorPcab', data.contractorPcab || '—');
    setTextContent('ongoingContractorCategory', data.contractorCategory || '—');
    setTextContent('ongoingContractorPcabExpiry', formatDate(data.contractorPcabExpiry));
    setTextContent('ongoingContractorPermit', data.contractorPermit || '—');
    setTextContent('ongoingContractorCity', data.contractorCity || '—');
    setTextContent('ongoingContractorPermitExpiry', formatDate(data.contractorPermitExpiry));
    setTextContent('ongoingContractorTin', data.contractorTin || '—');

    // Populate milestone timeline
    populateMilestoneTimeline('ongoingMilestoneTimeline', data.milestones);

    // Populate payment summary
    populatePaymentTable('ongoingPaymentTable', data.payments);

    // Show modal
    showModal(modal);
  }

  // Open Completed Project Modal
  async function openCompletedProjectModal(data) {
    const modal = document.getElementById('completedProjectModal');
    if (!modal) return;

    try {
      // Fetch HTML-rendered content from backend
      const response = await fetch(`/admin/project-management/${data.projectId}/completed-details`);
      if (!response.ok) throw new Error('Failed to fetch completed project details');

      const result = await response.json();

      if (result.success && result.html) {
        // Inject the rendered HTML into the modal
        const modalContainer = modal.querySelector('.absolute > div');
        if (modalContainer) {
          modalContainer.innerHTML = result.html;
        }

        // Show modal
        showModal(modal);

        // Re-attach action listeners
        attachCompletedModalListeners();
      } else {
        showNotification('Failed to load completed project details', 'error');
      }
    } catch (error) {
      console.error('Error loading completed project:', error);
      showNotification('An error occurred while loading project details', 'error');
    }
  }

  // Attach listeners for completed modal interactions
  function attachCompletedModalListeners() {
    // No additional listeners needed as onclick handlers are in the HTML
  }

  // Helper function to show milestone details
  window.showMilestoneDetails = function(itemId) {
    // Hide the default message
    const container = document.getElementById('completedDetailsContent');
    if (container) {
      const defaultMsg = container.querySelector('.text-gray-500');
      if (defaultMsg) defaultMsg.classList.add('hidden');
    }

    // Hide all detail divs
    const allDetails = document.querySelectorAll('[id^="milestone-detail-"]');
    allDetails.forEach(detail => detail.classList.add('hidden'));

    // Show the selected detail
    const selectedDetail = document.getElementById(`milestone-detail-${itemId}`);
    if (selectedDetail) {
      selectedDetail.classList.remove('hidden');
    }
  };

  // Toggle completed details visibility
  window.toggleCompletedDetails = function() {
    const detailsSection = document.getElementById('completedDetailsSection');
    if (detailsSection) {
      detailsSection.classList.toggle('hidden');
    }
  };

  // Open Halted Project Modal
  function openHaltedProjectModal(data) {
    const modal = document.getElementById('haltedProjectModal');
    if (!modal) return;

    // Populate basic info
    setTextContent('haltedProjectTitle', data.title);
    setTextContent('haltedProjectId', data.projectId);
    setTextContent('haltedOwnerName', data.ownerName);
    setTextContent('haltedContractorName', data.contractorName);
    setTextContent('haltedDate', formatDate(data.terminationData?.terminated_at));
    setTextContent('haltedReason', data.terminationData?.termination_reason || '—');
    setTextContent('haltedPropertyType', data.propertyType);
    setTextContent('haltedPropertyAddress', data.propertyAddress);
    setTextContent('haltedLotSize', data.lotSize ? `${data.lotSize} sqm` : '—');
    setTextContent('haltedFloorArea', data.floorArea ? `${data.floorArea} sqm` : '—');
    setTextContent('haltedBudget', formatBudget(data.budgetMin, data.budgetMax));
    setTextContent('haltedTimeline', data.timelineDisplay || '—');
    setTextContent('haltedDescription', data.description);

    // Populate contractor details
    setTextContent('haltedContractorEmail', data.contractorEmail || '—');
    setTextContent('haltedContractorPcab', data.contractorPcab || '—');
    setTextContent('haltedContractorCategory', data.contractorCategory || '—');

    // Populate milestones and payments
    populateMilestoneTimeline('haltedMilestoneTimeline', data.milestones);
    populatePaymentTable('haltedPaymentTable', data.payments);

    // Show modal
    showModal(modal);
  }

  // Open Cancelled Project Modal
  // Replaced with AJAX-based server-side rendering
  async function openCancelledProjectModal(data) {
    // Call the new showCancelledProjectModal function
    if (data && data.projectId) {
      await showCancelledProjectModal(data.projectId);
    }
  }

  // Helper functions for formatting
  function setTextContent(id, value) {
    const element = document.getElementById(id);
    if (element) {
      element.textContent = value || '—';
    }
  }

  function formatDate(dateString) {
    if (!dateString) return '—';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
  }

  function formatTimeline(min, max) {
    if (!min && !max) return '—';
    return `${min || '?'} - ${max || '?'}`;
  }

  function formatBudget(min, max) {
    if (!min && !max) return '—';
    const minFormatted = min ? `₱${parseFloat(min).toLocaleString()}` : '?';
    const maxFormatted = max ? `₱${parseFloat(max).toLocaleString()}` : '?';
    return `${minFormatted} - ${maxFormatted}`;
  }

  function getStatusClass(status) {
    const statusClasses = {
      'completed': 'bg-green-500',
      'in_progress': 'bg-blue-500',
      'not_started': 'bg-gray-400',
      'pending': 'bg-gray-400',
      'cancelled': 'bg-red-500',
      'rejected': 'bg-red-500',
      'delayed': 'bg-orange-500',
      'verified': 'bg-green-100 text-green-800',
      'unverified': 'bg-yellow-100 text-yellow-800',
      'approved': 'bg-green-100 text-green-800',
      'submitted': 'bg-blue-100 text-blue-800'
    };
    return statusClasses[status] || 'bg-gray-400';
  }

  function showModal(modal) {
    if (!modal) return;
    modal.classList.remove('hidden');
    const modalContent = modal.querySelector('.absolute > div, .modal-content');
    if (modalContent) {
      setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
      }, 10);
    }
    document.body.style.overflow = 'hidden';
  }

  function hideModal(modal) {
    if (!modal) return;
    const modalContent = modal.querySelector('.absolute > div, .modal-content');
    if (modalContent) {
      modalContent.classList.remove('scale-100', 'opacity-100');
      modalContent.classList.add('scale-95', 'opacity-0');
    }
    setTimeout(() => {
      modal.classList.add('hidden');
      document.body.style.overflow = 'auto';
    }, 300);
  }

  // Global modal close functions
  window.hideBiddingModal = function() {
    const modal = document.getElementById('biddingDetailsModal');
    hideModal(modal);
  };

  window.hideBiddingDetailsModal = function() {
    const modal = document.getElementById('biddingDetailsModal');
    hideModal(modal);
  };

  window.hideOngoingProjectModal = function() {
    const modal = document.getElementById('ongoingProjectModal');
    hideModal(modal);
  };

  window.hideCompletedProjectModal = function() {
    const modal = document.getElementById('completedProjectModal');
    hideModal(modal);
  };

  window.hideHaltedProjectModal = function() {
    const modal = document.getElementById('haltedProjectModal');
    hideModal(modal);
  };

  window.hideCancelledProjectModal = function() {
    const modal = document.getElementById('cancelledProjectModal');
    hideModal(modal);
  };

  function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-24 right-8 z-[60] px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-500 translate-x-full ${
      type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white font-semibold flex items-center gap-3`;
    notification.innerHTML = `
      <span>${message}</span>
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
      notification.style.transform = 'translateX(0)';
    }, 10);

    setTimeout(() => {
      notification.style.transform = 'translateX(150%)';
      setTimeout(() => notification.remove(), 500);
    }, 3000);
  }

  function addRipple(button, event) {
    const ripple = document.createElement('span');
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;

    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.classList.add('ripple');

    button.style.position = 'relative';
    button.style.overflow = 'hidden';
    button.appendChild(ripple);

    setTimeout(() => {
      ripple.remove();
    }, 600);
  }

  function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white font-medium z-50 transition-all duration-300 transform translate-x-full ${
      type === 'success' ? 'bg-green-500' :
      type === 'error' ? 'bg-red-500' :
      type === 'warning' ? 'bg-yellow-500' :
      'bg-blue-500'
    }`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
      notification.style.transform = 'translateX(0)';
    }, 10);

    setTimeout(() => {
      notification.style.transform = 'translateX(150%)';
      setTimeout(() => {
        notification.remove();
      }, 300);
    }, 3000);
  }

  // Show completion details modal - triggered from completed project modal
  window.showCompletionDetailsModal = async function(projectId) {
    try {
      const response = await fetch(`/admin/project-management/${projectId}/completion-details`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      if (!response.ok) throw new Error('Failed to fetch completion details');

      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML to extract just the modal content
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModal = doc.getElementById('completionDetailsModal');

        if (newModal) {
          // Get the existing modal container
          const existingModal = document.getElementById('completionDetailsModal');

          if (existingModal && existingModal.parentNode) {
            // Replace the entire modal element
            existingModal.parentNode.replaceChild(newModal, existingModal);

            // Show the modal by removing the 'hidden' class
            newModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load completion details', 'error');
      }
    } catch (error) {
      console.error('Error loading completion details:', error);
      showNotification('An error occurred while loading completion details', 'error');
    }
  };

  // Hide completion details modal
  window.hideCompletionDetailsModal = function() {
    const modal = document.getElementById('completionDetailsModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Show ongoing project modal with server-side rendered content
  window.showOngoingProjectModal = async function(projectId) {
    try {
      const response = await fetch(`/admin/project-management/${projectId}/ongoing-details`);
      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML string into a DOM element
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModalContent = doc.getElementById('ongoingProjectModal');

        if (newModalContent) {
          // Find the existing modal in the page
          const existingModal = document.getElementById('ongoingProjectModal');

          if (existingModal) {
            // Replace only the inner HTML to preserve scroll and avoid glitching
            existingModal.innerHTML = newModalContent.innerHTML;

            // Show the modal by removing the 'hidden' class
            existingModal.classList.remove('hidden');

            // Re-attach action listeners for milestone interactivity
            if (typeof window.attachActionListeners === 'function') {
              window.attachActionListeners();
            }
          }
        }
      } else {
        showNotification('Failed to load ongoing project details', 'error');
      }
    } catch (error) {
      console.error('Error loading ongoing project details:', error);
      showNotification('An error occurred while loading ongoing project details', 'error');
    }
  };

  // Hide ongoing project modal
  window.hideOngoingProjectModal = function() {
    const modal = document.getElementById('ongoingProjectModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Show open project modal with server-side rendered content
  window.showOpenProjectModal = async function(projectId) {
    try {
      const response = await fetch(`/admin/project-management/${projectId}/open-details`);
      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML string into a DOM element
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModalContent = doc.getElementById('biddingDetailsModal');

        if (newModalContent) {
          // Find the existing modal in the page
          const existingModal = document.getElementById('biddingDetailsModal');

          if (existingModal) {
            // Replace only the inner HTML to preserve scroll and avoid glitching
            existingModal.innerHTML = newModalContent.innerHTML;

            // Show the modal by removing the 'hidden' class
            existingModal.classList.remove('hidden');

            // Re-attach action listeners
            if (typeof window.attachActionListeners === 'function') {
              window.attachActionListeners();
            }
          }
        }
      } else {
        showNotification('Failed to load bidding project details', 'error');
      }
    } catch (error) {
      console.error('Error loading bidding project details:', error);
      showNotification('An error occurred while loading bidding project details', 'error');
    }
  };

  // Hide bidding modal
  window.hideBiddingModal = function() {
    const modal = document.getElementById('biddingDetailsModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Show bid status modal with server-side rendered content
  window.showBidStatusModal = async function(bidId) {
    try {
      const response = await fetch(`/admin/project-management/bids/${bidId}/details`);
      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML string into a DOM element
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModalContent = doc.getElementById('bidStatusModal');

        if (newModalContent) {
          // Find the existing modal in the page
          const existingModal = document.getElementById('bidStatusModal');

          if (existingModal) {
            // Replace only the inner HTML to preserve scroll and avoid glitching
            existingModal.innerHTML = newModalContent.innerHTML;

            // Show the modal by removing the 'hidden' class
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load bid details', 'error');
      }
    } catch (error) {
      console.error('Error loading bid details:', error);
      showNotification('An error occurred while loading bid details', 'error');
    }
  };

  // Hide bid status modal
  window.hideBidStatusModal = function() {
    const modal = document.getElementById('bidStatusModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Show accept bid modal with server-side rendered content
  window.showAcceptBidModal = async function(bidId) {
    try {
      const response = await fetch(`/admin/project-management/bids/${bidId}/accept-summary`);
      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML string into a DOM element
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModalContent = doc.getElementById('acceptBidModal');

        if (newModalContent) {
          // Find the existing modal in the page
          const existingModal = document.getElementById('acceptBidModal');

          if (existingModal) {
            // Replace only the inner HTML to preserve scroll and avoid glitching
            existingModal.innerHTML = newModalContent.innerHTML;

            // Show the modal by removing the 'hidden' class
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load bid summary', 'error');
      }
    } catch (error) {
      console.error('Error loading bid summary:', error);
      showNotification('An error occurred while loading bid summary', 'error');
    }
  };

  // Hide accept bid modal
  window.hideAcceptBidModal = function() {
    const modal = document.getElementById('acceptBidModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Confirm accept bid
  window.confirmAcceptBid = async function(bidId) {
    try {
      // Get CSRF token from meta tag or Laravel's global csrf token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';

      const response = await fetch(`/admin/project-management/bids/${bidId}/accept`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        }
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Bid accepted successfully', 'success');
        hideAcceptBidModal();
        hideBiddingModal();
        // Refresh the table to show updated status
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        showNotification(result.message || 'Failed to accept bid', 'error');
      }
    } catch (error) {
      console.error('Error accepting bid:', error);
      showNotification('An error occurred while accepting bid', 'error');
    }
  };

  // Show reject bid modal with server-side rendered content
  window.showRejectBidModal = async function(bidId) {
    try {
      const response = await fetch(`/admin/project-management/bids/${bidId}/reject-summary`);
      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML string into a DOM element
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModalContent = doc.getElementById('rejectBidModal');

        if (newModalContent) {
          // Find the existing modal in the page
          const existingModal = document.getElementById('rejectBidModal');

          if (existingModal) {
            // Replace only the inner HTML to preserve scroll and avoid glitching
            existingModal.innerHTML = newModalContent.innerHTML;

            // Show the modal by removing the 'hidden' class
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load bid summary', 'error');
      }
    } catch (error) {
      console.error('Error loading bid summary:', error);
      showNotification('An error occurred while loading bid summary', 'error');
    }
  };

  // Hide reject bid modal
  window.hideRejectBidModal = function() {
    const modal = document.getElementById('rejectBidModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Confirm reject bid
  window.confirmRejectBid = async function(bidId) {
    try {
      // Get the rejection reason from the textarea
      const reason = document.getElementById('rejectReason')?.value || '';

      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';

      const response = await fetch(`/admin/project-management/bids/${bidId}/reject`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ reason: reason })
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Bid rejected successfully', 'success');
        hideRejectBidModal();
        hideBiddingModal();
        // Refresh the table to show updated status
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        showNotification(result.message || 'Failed to reject bid', 'error');
      }
    } catch (error) {
      console.error('Error rejecting bid:', error);
      showNotification('An error occurred while rejecting bid', 'error');
    }
  };

  // Show ongoing milestone details (toggle visibility)
  window.showOngoingMilestoneDetails = function(itemId) {
    // Hide the default message
    const container = document.getElementById('ongoingDetailsContent');
    if (container) {
      const defaultMsg = container.querySelector('.text-gray-500');
      if (defaultMsg) defaultMsg.classList.add('hidden');
    }

    // Hide all milestone detail divs
    const allDetails = document.querySelectorAll('[id^="ongoing-milestone-detail-"]');
    allDetails.forEach(detail => detail.classList.add('hidden'));

    // Show the selected milestone detail
    const selectedDetail = document.getElementById(`ongoing-milestone-detail-${itemId}`);
    if (selectedDetail) {
      selectedDetail.classList.remove('hidden');
    }
  };

  // Show cancelled project modal with AJAX server-side rendering
  window.showCancelledProjectModal = async function(projectId) {
    try {
      const response = await fetch(`/admin/project-management/${projectId}/terminated-details`);
      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML string into a DOM element
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModalContent = doc.getElementById('cancelledProjectModal');

        if (newModalContent) {
          // Find the existing modal in the page
          const existingModal = document.getElementById('cancelledProjectModal');

          if (existingModal) {
            // Replace only the inner HTML to preserve scroll and avoid glitching
            existingModal.innerHTML = newModalContent.innerHTML;

            // Show the modal by removing the 'hidden' class
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load terminated project details', 'error');
      }
    } catch (error) {
      console.error('Error loading terminated project details:', error);
      showNotification('An error occurred while loading project details', 'error');
    }
  };

  // Hide cancelled project modal
  window.hideCancelledProjectModal = function() {
    const modal = document.getElementById('cancelledProjectModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Show terminated milestone details (interactive timeline)
  window.showTerminatedMilestoneDetail = function(itemId) {
    // Hide the default message if exists
    const container = document.getElementById('terminatedDetailsContent');
    if (container) {
      const defaultMsg = container.querySelector('.text-gray-500');
      if (defaultMsg) defaultMsg.classList.add('hidden');
    }

    // Hide all milestone detail divs
    const allDetails = document.querySelectorAll('[id^="term-detail-"]');
    allDetails.forEach(detail => detail.classList.add('hidden'));

    // Show the selected milestone detail
    const selectedDetail = document.getElementById(`term-detail-${itemId}`);
    if (selectedDetail) {
      selectedDetail.classList.remove('hidden');
    }
  };

  // Alias for consistency with Blade template
  window.showTerminatedMilestoneDetails = window.showTerminatedMilestoneDetail;

});






