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
    const restoreButtons = document.querySelectorAll('.restore-btn');

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
            showEditProjectModal(id);
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
            showDeleteProjectModal(id);
          }, 200);
        }
      });
    });

    restoreButtons.forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        addRipple(this, e);
        const id = this.getAttribute('data-id');
        if (id) {
          setTimeout(() => {
            showRestoreProjectModal(id);
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
    // Store the project ID globally when opening the modal
    window.currentProjectId = data.projectId;

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

        // Set the project ID on the modal element
        modal.dataset.projectId = data.projectId;

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
  // Replaced with AJAX-based server-side rendering
  async function openHaltedProjectModal(data) {
    // Call the new showHaltedProjectModal function
    if (data && data.projectId) {
      await showHaltedProjectModal(data.projectId);
    }
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
    // Store the project ID globally when opening the modal
    window.currentProjectId = projectId;

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
            // Set the project ID on the modal element
            existingModal.dataset.projectId = projectId;

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

  // Show delete project modal with server-side rendered content
  window.showDeleteProjectModal = async function(projectId) {
    try {
      if (!projectId) {
        showNotification('Invalid project ID', 'error');
        return;
      }

      const response = await fetch(`/admin/project-management/${projectId}/delete-summary`);
      const result = await response.json();

      if (result.success) {
        const modalContainer = document.getElementById('deleteProjectModalContainer');
        if (modalContainer) {
          modalContainer.innerHTML = result.html;
          const existingModal = document.getElementById('deleteProjectModal');
          if (existingModal) {
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load project summary', 'error');
      }
    } catch (error) {
      console.error('Error loading project summary:', error);
      showNotification('An error occurred while loading project summary', 'error');
    }
  };

  // Hide delete project modal
  window.hideDeleteProjectModal = function() {
    const modal = document.getElementById('deleteProjectModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Confirm delete project
  window.confirmDeleteProject = async function() {
    try {
      // Clear previous error
      const errorElement = document.getElementById('error-delete-reason');
      if (errorElement) {
        errorElement.classList.add('hidden');
        errorElement.textContent = '';
      }

      // Get project ID from modal
      const modal = document.getElementById('deleteProjectModal');
      const projectId = modal?.dataset?.projectId;

      if (!projectId) {
        showNotification('Project ID not found', 'error');
        return;
      }

      // Get the deletion reason from the textarea
      const reason = document.getElementById('deleteReason')?.value || '';

      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';

      const response = await fetch(`/admin/project-management/${projectId}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ reason: reason })
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Project deleted successfully', 'success');
        hideDeleteProjectModal();
        // Refresh the table to show updated status
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        // Handle validation errors
        if (response.status === 422 && result.errors) {
          if (result.errors.reason && errorElement) {
            errorElement.textContent = result.errors.reason[0];
            errorElement.classList.remove('hidden');
          }
          // No toast notification for validation errors - error shown below field
        } else {
          showNotification(result.message || 'Failed to delete project', 'error');
        }
      }
    } catch (error) {
      console.error('Error deleting project:', error);

      // If it's a network error, show in toast
      if (error.name === 'TypeError' && error.message === 'Failed to fetch') {
        showNotification('Network error - please check your connection and try again', 'error');
      } else {
        showNotification('An error occurred while deleting project', 'error');
      }
    }
  };

  // Show restore project modal with server-side rendered content
  window.showRestoreProjectModal = async function(projectId) {
    try {
      if (!projectId) {
        showNotification('Invalid project ID', 'error');
        return;
      }

      const response = await fetch(`/admin/project-management/${projectId}/restore-summary`);
      const result = await response.json();

      if (result.success) {
        const modalContainer = document.getElementById('restoreProjectModalContainer');
        if (modalContainer) {
          modalContainer.innerHTML = result.html;
          const existingModal = document.getElementById('restoreProjectModal');
          if (existingModal) {
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load project summary', 'error');
      }
    } catch (error) {
      console.error('Error loading project summary:', error);
      showNotification('An error occurred while loading project summary', 'error');
    }
  };

  // Hide restore project modal
  window.hideRestoreProjectModal = function() {
    const modal = document.getElementById('restoreProjectModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Confirm restore project
  window.confirmRestoreProject = async function() {
    try {
      // Get project ID from modal
      const modal = document.getElementById('restoreProjectModal');
      const projectId = modal?.dataset?.projectId;

      if (!projectId) {
        showNotification('Project ID not found', 'error');
        return;
      }

      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';

      const response = await fetch(`/admin/project-management/${projectId}/restore`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Project restored successfully', 'success');
        hideRestoreProjectModal();
        // Refresh the table to show updated status
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        showNotification(result.message || 'Failed to restore project', 'error');
      }
    } catch (error) {
      console.error('Error restoring project:', error);

      // If it's a network error, show in toast
      if (error.name === 'TypeError' && error.message === 'Failed to fetch') {
        showNotification('Network error - please check your connection and try again', 'error');
      } else {
        showNotification('An error occurred while restoring project', 'error');
      }
    }
  };

  // Show milestone details for completed project modal
  window.showMilestoneDetails = function(itemId) {
    // Store the selected item ID globally
    window.selectedMilestoneItemId = itemId;

    // Show the edit button
    const editBtn = document.getElementById('editMilestoneBtn');
    if (editBtn) {
      editBtn.classList.remove('hidden');
    }

    // Note: The details are already rendered in the completedModalContent template
    // This function just enables the edit button for the selected milestone
  };

  // Show ongoing milestone details (toggle visibility)
  window.showOngoingMilestoneDetails = function(itemId) {
    // Store the selected item ID globally
    window.selectedMilestoneItemId = itemId;

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

    // Show the edit button
    const editBtn = document.getElementById('editOngoingMilestoneBtn');
    if (editBtn) {
      editBtn.classList.remove('hidden');
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

  // Show halted project modal with AJAX server-side rendering
  window.showHaltedProjectModal = async function(projectId) {
    try {
      const response = await fetch(`/admin/project-management/${projectId}/halted-details`);
      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML string into a DOM element
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModalContent = doc.getElementById('haltedProjectModal');

        if (newModalContent) {
          // Find the existing modal in the page
          const existingModal = document.getElementById('haltedProjectModal');

          if (existingModal) {
            // Replace only the inner HTML to preserve scroll and avoid glitching
            existingModal.innerHTML = newModalContent.innerHTML;

            // Show the modal by removing the 'hidden' class
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load halted project details', 'error');
      }
    } catch (error) {
      console.error('Error loading halted project details:', error);
      showNotification('An error occurred while loading project details', 'error');
    }
  };

  // Hide halted project modal
  window.hideHaltedProjectModal = function() {
    const modal = document.getElementById('haltedProjectModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Show halted milestone details (interactive timeline)
  window.showHaltedMilestoneDetail = function(itemId) {
    // Hide the default message if exists
    const container = document.getElementById('haltedDetailsContent');
    if (container) {
      const defaultMsg = container.querySelector('.text-gray-500');
      if (defaultMsg) defaultMsg.classList.add('hidden');
    }

    // Hide all milestone detail divs
    const allDetails = document.querySelectorAll('[id^="halted-milestone-detail-"]');
    allDetails.forEach(detail => detail.classList.add('hidden'));

    // Show the selected milestone detail
    const selectedDetail = document.getElementById(`halted-milestone-detail-${itemId}`);
    if (selectedDetail) {
      selectedDetail.classList.remove('hidden');
    }
  };

  // Show halt details modal (administrative information)
  window.showHaltDetailsModal = async function(projectId) {
    try {
      const response = await fetch(`/admin/project-management/${projectId}/halt-details`);
      const result = await response.json();

      if (result.success && result.html) {
        // Parse the HTML string into a DOM element
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newModalContent = doc.getElementById('haltDetailsModal');

        if (newModalContent) {
          // Find the existing modal in the page
          const existingModal = document.getElementById('haltDetailsModal');

          if (existingModal) {
            // Replace only the inner HTML to preserve scroll and avoid glitching
            existingModal.innerHTML = newModalContent.innerHTML;

            // Store the project ID on the modal for later use
            existingModal.setAttribute('data-project-id', projectId);

            // Show the modal by removing the 'hidden' class
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load halt details', 'error');
      }
    } catch (error) {
      console.error('Error loading halt details:', error);
      showNotification('An error occurred while loading halt details', 'error');
    }
  };

  // Hide halt details modal
  window.hideHaltDetailsModal = function() {
    const modal = document.getElementById('haltDetailsModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Show cancel halt confirmation modal
  window.showCancelHaltConfirm = function() {
    const modal = document.getElementById('cancelHaltConfirmModal');
    if (modal) {
      modal.classList.remove('hidden');
    }
  };

  // Hide cancel halt confirmation modal
  window.hideCancelHaltConfirm = function() {
    const modal = document.getElementById('cancelHaltConfirmModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Show resume halt confirmation modal
  window.showResumeHaltConfirm = function() {
    const modal = document.getElementById('resumeHaltConfirmModal');
    if (modal) {
      modal.classList.remove('hidden');
    }
  };

  // Hide resume halt confirmation modal
  window.hideResumeHaltConfirm = function() {
    const modal = document.getElementById('resumeHaltConfirmModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Clear error when user types in the textarea
  const remarksInput = document.getElementById('cancelHaltRemarks');
  const remarksError = document.getElementById('cancelHaltRemarksError');

  if (remarksInput && remarksError) {
    remarksInput.addEventListener('input', function() {
      remarksError.textContent = '';
      remarksError.classList.add('hidden');
      remarksInput.classList.remove('border-red-500');
      remarksInput.classList.add('border-gray-300');
    });
  }

  // Confirm cancel halt - change project status to terminated
  window.confirmCancelHalt = async function() {
    const haltDetailsModal = document.getElementById('haltDetailsModal');
    const projectIdMatch = haltDetailsModal?.getAttribute('data-project-id');
    const remarksInput = document.getElementById('cancelHaltRemarks');
    const remarksError = document.getElementById('cancelHaltRemarksError');
    const remarks = remarksInput?.value.trim();

    // Clear previous error state first
    if (remarksError) {
      remarksError.textContent = '';
      remarksError.classList.add('hidden');
    }
    if (remarksInput) {
      remarksInput.classList.remove('border-red-500');
      remarksInput.classList.add('border-gray-300');
    }

    if (!projectIdMatch) {
      showNotification('Project ID not found', 'error');
      return;
    }

    // Validate remarks
    if (!remarks) {
      if (remarksError) {
        remarksError.textContent = 'A reason for termination is required.';
        remarksError.classList.remove('hidden');
      }
      if (remarksInput) {
        remarksInput.classList.remove('border-gray-300');
        remarksInput.classList.add('border-red-500');
        remarksInput.focus();
      }
      return;
    }

    // Validate 10-character minimum
    if (remarks.length < 10) {
      if (remarksError) {
        remarksError.textContent = 'The reason must be at least 10 characters.';
        remarksError.classList.remove('hidden');
      }
      if (remarksInput) {
        remarksInput.classList.remove('border-gray-300');
        remarksInput.classList.add('border-red-500');
        remarksInput.focus();
      }
      return;
    }

    try {
      const response = await fetch(`/admin/project-management/${projectIdMatch}/cancel-halt`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          remarks: remarks
        })
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Project terminated successfully', 'success');
        // Clear the remarks input and error
        if (remarksInput) remarksInput.value = '';
        if (remarksError) {
          remarksError.textContent = '';
          remarksError.classList.add('hidden');
        }
        hideCancelHaltConfirm();
        hideHaltDetailsModal();
        hideHaltedProjectModal();
        // Refresh the projects table
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        // Show server validation errors below input
        if (result.errors && result.errors.remarks) {
          if (remarksError) {
            remarksError.textContent = result.errors.remarks[0];
            remarksError.classList.remove('hidden');
          }
          if (remarksInput) {
            remarksInput.classList.remove('border-gray-300');
            remarksInput.classList.add('border-red-500');
          }
        } else {
          showNotification(result.message || 'Failed to terminate project', 'error');
        }
      }
    } catch (error) {
      console.error('Error terminating project:', error);
      showNotification('An error occurred while terminating the project', 'error');
    }
  };

  // Confirm resume halt - change project status back to ongoing
  window.confirmResumeHalt = async function() {
    const haltDetailsModal = document.getElementById('haltDetailsModal');
    const projectIdMatch = haltDetailsModal?.getAttribute('data-project-id');

    if (!projectIdMatch) {
      showNotification('Project ID not found', 'error');
      return;
    }

    try {
      const response = await fetch(`/admin/project-management/${projectIdMatch}/resume-halt`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Project resumed successfully', 'success');
        hideResumeHaltConfirm();
        hideHaltDetailsModal();
        hideHaltedProjectModal();
        // Refresh the projects table
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        showNotification(result.message || 'Failed to resume project', 'error');
      }
    } catch (error) {
      console.error('Error resuming project:', error);
      showNotification('An error occurred while resuming the project', 'error');
    }
  };

  // Show Edit Project Modal (AJAX Load)
  window.showEditProjectModal = async function(projectId) {
    const modal = document.getElementById('editProjectModal');
    if (!modal) return;

    try {
      const response = await fetch(`/admin/project-management/${projectId}/edit`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const result = await response.json();

      if (result.success && result.html) {
        // Parse and inject HTML
        const parser = new DOMParser();
        const doc = parser.parseFromString(result.html, 'text/html');
        const newContent = doc.body.firstChild;

        const existingContent = modal.querySelector('.bg-white');
        if (existingContent) {
          existingContent.replaceWith(newContent);
        } else {
          const wrapper = modal.querySelector('.flex.items-center.justify-center');
          if (wrapper) {
            wrapper.innerHTML = '';
            wrapper.appendChild(newContent);
          }
        }

        // Attach contractor preview handler
        attachContractorPreviewHandler();

        // Initialize PSGC location dropdowns
        initializeEditLocationDropdowns();

        // Show modal
        modal.classList.remove('hidden');
      } else {
        showNotification('Failed to load project details', 'error');
      }
    } catch (error) {
      console.error('Error loading edit project modal:', error);
      showNotification('An error occurred while loading the modal', 'error');
    }
  };

  // Hide Edit Project Modal
  window.hideEditProjectModal = function() {
    const modal = document.getElementById('editProjectModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Attach contractor preview handler
  function attachContractorPreviewHandler() {
    const contractorSelect = document.getElementById('editContractorSelect');
    const preview = document.getElementById('editContractorPreview');
    const bidInfo = document.getElementById('editBidInfo');

    if (!contractorSelect || !preview) return;

    contractorSelect.addEventListener('change', function() {
      const selectedOption = this.options[this.selectedIndex];

      if (this.value && this.value !== '') {
        // Show preview with selected contractor details
        preview.classList.remove('hidden');

        document.getElementById('previewCompany').textContent = selectedOption.getAttribute('data-company') || '—';
        document.getElementById('previewType').textContent = selectedOption.getAttribute('data-type') || '—';
        document.getElementById('previewPhone').textContent = selectedOption.getAttribute('data-phone') || '—';
        document.getElementById('previewEmail').textContent = selectedOption.getAttribute('data-email') || '—';
        document.getElementById('previewAddress').textContent = selectedOption.getAttribute('data-address') || '—';
        document.getElementById('previewPCAB').textContent = selectedOption.getAttribute('data-pcab') || '—';
        document.getElementById('previewPCABCategory').textContent = selectedOption.getAttribute('data-pcab-category') || '—';
        document.getElementById('previewPermit').textContent = selectedOption.getAttribute('data-permit') || '—';
        document.getElementById('previewPermitCity').textContent = selectedOption.getAttribute('data-permit-city') || '—';
        document.getElementById('previewTIN').textContent = selectedOption.getAttribute('data-tin') || '—';
        document.getElementById('previewExperience').textContent = selectedOption.getAttribute('data-experience')
          ? `${selectedOption.getAttribute('data-experience')} years`
          : '—';
        document.getElementById('previewBid').textContent = selectedOption.getAttribute('data-bid')
          ? `₱${parseFloat(selectedOption.getAttribute('data-bid')).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`
          : '—';

        // Show and populate bid information
        if (bidInfo) {
          bidInfo.classList.remove('hidden');

          // Proposed cost
          const cost = selectedOption.getAttribute('data-bid');
          document.getElementById('bidCost').textContent = cost
            ? `₱${parseFloat(cost).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`
            : '—';

          // Estimated timeline
          const timeline = selectedOption.getAttribute('data-timeline');
          document.getElementById('bidTimeline').textContent = timeline ? `${timeline} months` : '—';

          // Contractor notes
          const notes = selectedOption.getAttribute('data-notes');
          document.getElementById('bidNotes').textContent = notes || 'No notes provided';

          // Bid files
          const filesData = selectedOption.getAttribute('data-files');
          const bidFilesList = document.getElementById('bidFilesList');
          const bidFilesContainer = document.getElementById('bidFilesContainer');

          console.log('Bid files data:', filesData); // Debug log

          if (filesData) {
            try {
              const files = JSON.parse(filesData);
              console.log('Parsed files:', files); // Debug log

              if (files && files.length > 0) {
                bidFilesContainer.style.display = 'block';
                bidFilesList.innerHTML = '';

                files.forEach(file => {
                  const fileDiv = document.createElement('div');
                  fileDiv.className = 'flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200';
                  fileDiv.innerHTML = `
                    <div class="flex items-center gap-3">
                      <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                      </svg>
                      <div>
                        <span class="text-sm text-gray-700 font-medium">${file.file_name || 'File'}</span>
                        ${file.description ? `<p class="text-xs text-gray-500">${file.description}</p>` : ''}
                      </div>
                    </div>
                    <a href="/storage/${file.file_path}" target="_blank" class="text-green-600 hover:text-green-700 text-sm font-medium">
                      View
                    </a>
                  `;
                  bidFilesList.appendChild(fileDiv);
                });
              } else {
                console.log('No files in array');
                bidFilesContainer.style.display = 'none';
              }
            } catch (e) {
              console.error('Error parsing bid files:', e);
              bidFilesContainer.style.display = 'none';
            }
          } else {
            console.log('No files data attribute');
            bidFilesContainer.style.display = 'none';
          }
        }
      } else {
        // Hide preview and bid info
        preview.classList.add('hidden');
        if (bidInfo) {
          bidInfo.classList.add('hidden');
        }
      }
    });
  }

  // Show Edit Project Confirmation Modal (placeholder - implement as needed)
  window.showEditProjectConfirmModal = async function() {
    // Clear previous errors
    document.querySelectorAll('[id^="error-"]').forEach(el => {
      el.classList.add('hidden');
      el.textContent = '';
    });

    // Get project ID
    const modalContent = document.querySelector('#editProjectModal .bg-white');
    const projectId = modalContent?.dataset?.projectId;

    if (!projectId) {
      showNotification('Project ID not found', 'error');
      return;
    }

    // Collect form data
    const projectData = {
      project_title: document.getElementById('editProjectTitle')?.value,
      project_description: document.getElementById('editProjectDescription')?.value,
      property_type: document.getElementById('editPropertyType')?.value,
      lot_size: document.getElementById('editLotSize')?.value,
      floor_area: document.getElementById('editFloorArea')?.value,
      selected_contractor_id: document.getElementById('editContractorSelect')?.value || null
    };

    // Build location string from dropdowns
    const province = document.getElementById('editProvince');
    const city = document.getElementById('editCity');
    const barangay = document.getElementById('editBarangay');
    const street = document.getElementById('editStreet')?.value || '';
    const postalCode = document.getElementById('editPostalCode')?.value || '';

    const locationParts = [];
    if (street) locationParts.push(street);
    if (barangay?.selectedOptions[0]?.text && barangay.value) locationParts.push(barangay.selectedOptions[0].text);
    if (city?.selectedOptions[0]?.text && city.value) locationParts.push(city.selectedOptions[0].text);

    // Combine province and postal code in last part
    if (province?.selectedOptions[0]?.text && province.value) {
      const provinceName = province.selectedOptions[0].text;
      locationParts.push(postalCode ? `${provinceName} ${postalCode}` : provinceName);
    } else if (postalCode) {
      locationParts.push(postalCode);
    }

    projectData.project_location = locationParts.join(', ');

    try {
      const response = await fetch(`/admin/project-management/${projectId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(projectData)
      });

      const result = await response.json();

      if (result.success) {
        showNotification(result.message || 'Project updated successfully', 'success');
        hideEditProjectModal();
        // Refresh the table to show updated data
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        // Handle validation errors
        if (response.status === 422 && result.errors) {
          Object.keys(result.errors).forEach(field => {
            const errorElement = document.getElementById(`error-${field}`);
            if (errorElement) {
              errorElement.textContent = result.errors[field][0];
              errorElement.classList.remove('hidden');
            }
          });
          // No toast notification for validation errors - errors shown below fields
        } else {
          showNotification(result.message || 'Failed to update project', 'error');
        }
      }
    } catch (error) {
      console.error('Error updating project:', error);
      showNotification('An error occurred while updating the project', 'error');
    }
  };

  // Initialize PSGC location dropdowns for edit modal
  async function initializeEditLocationDropdowns() {
    const provinceSelect = document.getElementById('editProvince');
    const citySelect = document.getElementById('editCity');
    const barangaySelect = document.getElementById('editBarangay');
    const streetInput = document.getElementById('editStreet');
    const postalCodeInput = document.getElementById('editPostalCode');

    if (!provinceSelect) return;

    // Parse existing location from hidden input or data attribute
    const existingLocation = document.getElementById('editProjectTitle')?.closest('.bg-white')?.dataset?.location || '';
    const locationParts = existingLocation.split(',').map(part => part.trim()).filter(Boolean);

    // Extract parts: street, barangay, city, "province postalcode"
    let street = '', barangay = '', city = '', province = '', postalCode = '';

    if (locationParts.length >= 4) {
      street = locationParts[0];
      barangay = locationParts[1];
      city = locationParts[2];

      // Last part contains "Province Name PostalCode"
      const provinceAndPostal = locationParts[3];
      const lastSpaceIndex = provinceAndPostal.lastIndexOf(' ');

      if (lastSpaceIndex > -1) {
        province = provinceAndPostal.substring(0, lastSpaceIndex).trim();
        postalCode = provinceAndPostal.substring(lastSpaceIndex + 1).trim();
      } else {
        province = provinceAndPostal;
      }
    } else if (locationParts.length === 3) {
      barangay = locationParts[0];
      city = locationParts[1];

      const provinceAndPostal = locationParts[2];
      const lastSpaceIndex = provinceAndPostal.lastIndexOf(' ');

      if (lastSpaceIndex > -1) {
        province = provinceAndPostal.substring(0, lastSpaceIndex).trim();
        postalCode = provinceAndPostal.substring(lastSpaceIndex + 1).trim();
      } else {
        province = provinceAndPostal;
      }
    } else if (locationParts.length === 2) {
      city = locationParts[0];

      const provinceAndPostal = locationParts[1];
      const lastSpaceIndex = provinceAndPostal.lastIndexOf(' ');

      if (lastSpaceIndex > -1) {
        province = provinceAndPostal.substring(0, lastSpaceIndex).trim();
        postalCode = provinceAndPostal.substring(lastSpaceIndex + 1).trim();
      } else {
        province = provinceAndPostal;
      }
    } else if (locationParts.length === 1) {
      const provinceAndPostal = locationParts[0];
      const lastSpaceIndex = provinceAndPostal.lastIndexOf(' ');

      if (lastSpaceIndex > -1) {
        province = provinceAndPostal.substring(0, lastSpaceIndex).trim();
        postalCode = provinceAndPostal.substring(lastSpaceIndex + 1).trim();
      } else {
        province = provinceAndPostal;
      }
    }

    // Populate text fields immediately
    if (streetInput) streetInput.value = street;
    if (postalCodeInput) postalCodeInput.value = postalCode;

    console.log('Parsed location:', { street, barangay, city, province, postalCode });

    // Load provinces
    try {
      const response = await fetch('/api/psgc/provinces');
      const provinces = await response.json();

      provinceSelect.innerHTML = '<option value="">Select Province</option>';
      let selectedProvinceCode = null;

      provinces.forEach(prov => {
        const option = document.createElement('option');
        option.value = prov.code;
        option.textContent = prov.name;

        // Match by name (case-insensitive, flexible matching)
        const normalizedProvName = prov.name.toLowerCase().replace(/\s+/g, ' ').trim();
        const normalizedSearchName = province.toLowerCase().replace(/\s+/g, ' ').trim();

        if (province && (normalizedProvName === normalizedSearchName ||
                        normalizedProvName.includes(normalizedSearchName) ||
                        normalizedSearchName.includes(normalizedProvName))) {
          option.selected = true;
          selectedProvinceCode = prov.code;
          console.log('Matched province:', prov.name, 'with code:', prov.code);
        }

        provinceSelect.appendChild(option);
      });

      // If province was selected, load cities
      if (selectedProvinceCode) {
        await loadCities(selectedProvinceCode, city, barangay);
      } else if (province) {
        console.warn('Province not matched:', province);
      }
    } catch (error) {
      console.error('Error loading provinces:', error);
    }

    // Helper function to load cities
    async function loadCities(provinceCode, cityToSelect = '', barangayToSelect = '') {
      citySelect.innerHTML = '<option value="">Select City</option>';
      citySelect.disabled = false;

      try {
        const response = await fetch(`/api/psgc/provinces/${provinceCode}/cities`);
        const cities = await response.json();
        let selectedCityCode = null;

        cities.forEach(cty => {
          const option = document.createElement('option');
          option.value = cty.code;
          option.textContent = cty.name;

          // Match by name (case-insensitive, flexible matching)
          const normalizedCityName = cty.name.toLowerCase().replace(/\s+/g, ' ').trim();
          const normalizedSearchName = cityToSelect.toLowerCase().replace(/\s+/g, ' ').trim();

          if (cityToSelect && (normalizedCityName === normalizedSearchName ||
                              normalizedCityName.includes(normalizedSearchName) ||
                              normalizedSearchName.includes(normalizedCityName))) {
            option.selected = true;
            selectedCityCode = cty.code;
            console.log('Matched city:', cty.name, 'with code:', cty.code);
          }

          citySelect.appendChild(option);
        });

        // If city was selected, load barangays
        if (selectedCityCode) {
          await loadBarangays(selectedCityCode, barangayToSelect);
        } else if (cityToSelect) {
          console.warn('City not matched:', cityToSelect);
        }
      } catch (error) {
        console.error('Error loading cities:', error);
      }
    }

    // Helper function to load barangays
    async function loadBarangays(cityCode, barangayToSelect = '') {
      barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
      barangaySelect.disabled = false;

      try {
        const response = await fetch(`/api/psgc/cities/${cityCode}/barangays`);
        const barangays = await response.json();

        barangays.forEach(brgy => {
          const option = document.createElement('option');
          option.value = brgy.code;
          option.textContent = brgy.name;

          // Match by name (case-insensitive, flexible matching)
          const normalizedBrgyName = brgy.name.toLowerCase().replace(/\s+/g, ' ').trim();
          const normalizedSearchName = barangayToSelect.toLowerCase().replace(/\s+/g, ' ').trim();

          if (barangayToSelect && (normalizedBrgyName === normalizedSearchName ||
                                  normalizedBrgyName.includes(normalizedSearchName) ||
                                  normalizedSearchName.includes(normalizedBrgyName))) {
            option.selected = true;
            console.log('Matched barangay:', brgy.name, 'with code:', brgy.code);
          }

          barangaySelect.appendChild(option);
        });

        if (barangayToSelect && !barangaySelect.value) {
          console.warn('Barangay not matched:', barangayToSelect);
        }
      } catch (error) {
        console.error('Error loading barangays:', error);
      }
    }

    // Province change handler
    provinceSelect.addEventListener('change', async function() {
      citySelect.innerHTML = '<option value="">Select City</option>';
      barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
      citySelect.disabled = !this.value;
      barangaySelect.disabled = true;

      if (this.value) {
        await loadCities(this.value);
      }
    });

    // City change handler
    citySelect.addEventListener('change', async function() {
      barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
      barangaySelect.disabled = !this.value;

      if (this.value) {
        await loadBarangays(this.value);
      }
    });
  }

  // Show edit milestone item modal with server-side rendered content
  window.openEditMilestoneModal = async function(itemId) {
    try {
      if (!itemId) {
        showNotification('Invalid milestone item ID', 'error');
        return;
      }

      const response = await fetch(`/admin/project-management/milestone-item/${itemId}/edit`);
      const result = await response.json();

      if (result.success) {
        const modalContainer = document.getElementById('editMilestoneModalContainer');
        if (modalContainer) {
          modalContainer.innerHTML = result.html;
          const existingModal = document.getElementById('editMilestoneModal');
          if (existingModal) {
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load milestone item', 'error');
      }
    } catch (error) {
      console.error('Error loading milestone item:', error);
      showNotification('An error occurred while loading milestone item', 'error');
    }
  };

  // Hide edit milestone modal
  window.hideEditMilestoneModal = function() {
    const modal = document.getElementById('editMilestoneModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Save milestone edit
  window.saveMilestoneEdit = async function() {
    try {
      // Clear all previous errors
      const errorElements = document.querySelectorAll('[id^="error-"]');
      errorElements.forEach(el => {
        el.classList.add('hidden');
        el.textContent = '';
      });

      // Get item ID from modal
      const modal = document.getElementById('editMilestoneModal');
      const itemId = modal?.dataset?.itemId;

      if (!itemId) {
        showNotification('Item ID not found', 'error');
        return;
      }

      // Get form values
      const milestone_item_title = document.getElementById('editMilestoneItemTitle')?.value || '';
      const milestone_item_description = document.getElementById('editMilestoneItemDescription')?.value || '';
      const date_to_finish = document.getElementById('editMilestoneItemDate')?.value || '';
      const milestone_item_cost = document.getElementById('editMilestoneItemCost')?.value || '';
      const item_status = document.getElementById('editMilestoneItemStatus')?.value || '';

      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

      const response = await fetch(`/admin/project-management/milestone-item/${itemId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          milestone_item_title,
          milestone_item_description,
          date_to_finish,
          milestone_item_cost,
          item_status
        })
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Milestone item updated successfully', 'success');
        hideEditMilestoneModal();

        // Refresh the currently open modal with updated data
        if (window.currentProjectId) {
          const selectedItemId = window.selectedMilestoneItemId;

          // Determine which modal is open and refresh it
          const ongoingModal = document.getElementById('ongoingProjectModal');
          const completedModal = document.getElementById('completedProjectModal');

          if (ongoingModal && !ongoingModal.classList.contains('hidden')) {
            // Refresh ongoing project modal
            await showOngoingProjectModal(window.currentProjectId);
            // Re-select the milestone after refresh
            if (selectedItemId) {
              setTimeout(() => showOngoingMilestoneDetails(selectedItemId), 300);
            }
          } else if (completedModal && !completedModal.classList.contains('hidden')) {
            // Refresh completed project modal
            const detailsResponse = await fetch(`/admin/project-management/${window.currentProjectId}/details`);
            const detailsResult = await detailsResponse.json();
            if (detailsResult.success && detailsResult.data) {
              await openCompletedProjectModal(detailsResult.data);
              // Re-select the milestone after refresh
              if (selectedItemId) {
                setTimeout(() => showMilestoneDetails(selectedItemId), 300);
              }
            }
          }
        }
      } else {
        // Handle validation errors
        if (response.status === 422 && result.errors) {
          // Map validation errors to error elements
          const errorMap = {
            'milestone_item_title': 'error-milestone-item-title',
            'milestone_item_description': 'error-milestone-item-description',
            'date_to_finish': 'error-date-to-finish',
            'milestone_item_cost': 'error-milestone-item-cost',
            'item_status': 'error-item-status'
          };

          Object.keys(result.errors).forEach(field => {
            const errorElementId = errorMap[field];
            if (errorElementId) {
              const errorElement = document.getElementById(errorElementId);
              if (errorElement) {
                errorElement.textContent = result.errors[field][0];
                errorElement.classList.remove('hidden');
              }
            }
          });
          // No toast notification for validation errors
        } else {
          showNotification(result.message || 'Failed to update milestone item', 'error');
        }
      }
    } catch (error) {
      console.error('Error updating milestone item:', error);
      showNotification('An error occurred while updating milestone item', 'error');
    }
  };

  // Show halt project modal with server-side rendered content
  window.showHaltProjectModal = async function(projectId) {
    try {
      if (!projectId) {
        showNotification('Invalid project ID', 'error');
        return;
      }

      const response = await fetch(`/admin/project-management/${projectId}/halt-summary`);
      const result = await response.json();

      if (result.success) {
        const modalContainer = document.getElementById('haltProjectModalContainer');
        if (modalContainer) {
          modalContainer.innerHTML = result.html;
          const existingModal = document.getElementById('haltProjectModal');
          if (existingModal) {
            existingModal.classList.remove('hidden');
          }
        }
      } else {
        showNotification('Failed to load project summary', 'error');
      }
    } catch (error) {
      console.error('Error loading project summary:', error);
      showNotification('An error occurred while loading project summary', 'error');
    }
  };

  // Hide halt project modal
  window.hideHaltProjectModal = function() {
    const modal = document.getElementById('haltProjectModal');
    if (modal) {
      modal.classList.add('hidden');
    }
  };

  // Confirm halt project
  window.confirmHaltProject = async function() {
    try {
      // Clear previous errors
      const errorElement = document.getElementById('error-halt-reason');
      if (errorElement) {
        errorElement.classList.add('hidden');
        errorElement.textContent = '';
      }
      const disputeError = document.getElementById('error-halt-dispute');
      if (disputeError) {
        disputeError.classList.add('hidden');
        disputeError.textContent = '';
      }

      // Get project ID from modal
      const modal = document.getElementById('haltProjectModal');
      const projectId = modal?.dataset?.projectId;

      if (!projectId) {
        showNotification('Project ID not found', 'error');
        return;
      }

      // Get the halt reason and remarks from the textareas
      const haltReason = document.getElementById('haltReason')?.value || '';
      const haltRemarks = document.getElementById('haltRemarks')?.value || '';
      const haltDispute = document.getElementById('haltDispute')?.value || '';

      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';

      const response = await fetch(`/admin/project-management/${projectId}/halt`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          dispute_id: haltDispute,
          halt_reason: haltReason,
          project_remarks: haltRemarks
        })
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Project halted successfully', 'success');
        hideHaltProjectModal();
        // Close the ongoing/bidding modal if open
        hideOngoingProjectModal();
        hideBiddingModal();
        // Refresh the table to show updated status
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        // Handle validation errors
        if (response.status === 422 && result.errors) {
          // Show dispute error if present
          const disputeError = document.getElementById('error-halt-dispute');
          if (result.errors.dispute_id && disputeError) {
            disputeError.textContent = result.errors.dispute_id[0];
            disputeError.classList.remove('hidden');
          }
          // Show halt reason error if present
          if (result.errors.halt_reason && errorElement) {
            errorElement.textContent = result.errors.halt_reason[0];
            errorElement.classList.remove('hidden');
          }
          // No toast notification for validation errors - error shown below field
        } else {
          showNotification(result.message || 'Failed to halt project', 'error');
        }
      }
    } catch (error) {
      console.error('Error halting project:', error);

      // If it's a network error, show in toast
      if (error.name === 'TypeError' && error.message === 'Failed to fetch') {
        showNotification('Network error - please check your connection and try again', 'error');
      } else {
        showNotification('An error occurred while halting project', 'error');
      }
    }
  };

  // Resume halted project
  window.resumeHaltedProject = async function(projectId) {
    if (!confirm('Are you sure you want to resume this project? The project and all halted milestone items will be restored to their previous status.')) {
      return;
    }

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';

      const response = await fetch(`/admin/project-management/${projectId}/resume`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Project resumed successfully', 'success');
        hideHaltedProjectModal();
        // Refresh the table to show updated status
        if (typeof window.refreshProjectsTable === 'function') {
          window.refreshProjectsTable();
        }
      } else {
        showNotification(result.message || 'Failed to resume project', 'error');
      }
    } catch (error) {
      console.error('Error resuming project:', error);
      showNotification('An error occurred while resuming project', 'error');
    }
  };

});
