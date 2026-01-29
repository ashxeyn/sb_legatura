// AI Management Interactive JavaScript

document.addEventListener('DOMContentLoaded', function() {
  // Modal elements
  const aiAnalysisModal = document.getElementById('aiAnalysisModal');
  const closeAiAnalysisModal = document.getElementById('closeAiAnalysisModal');
  const reanalyzeConfirmModal = document.getElementById('reanalyzeConfirmModal');
  const implementedConfirmModal = document.getElementById('implementedConfirmModal');
  const deleteAiProjectModal = document.getElementById('deleteAiProjectModal');
  const deleteActivityModal = document.getElementById('deleteActivityModal');

  // Buttons
  const viewAiBtns = document.querySelectorAll('.view-ai-btn');
  const deleteAiBtns = document.querySelectorAll('.delete-ai-btn');
  const deleteActivityBtns = document.querySelectorAll('.delete-activity-btn');
  const reanalyzeBtn = document.getElementById('reanalyzeBtn');
  const implementedBtn = document.getElementById('implementedBtn');
  const cancelReanalyze = document.getElementById('cancelReanalyze');
  const confirmReanalyze = document.getElementById('confirmReanalyze');
  const cancelImplemented = document.getElementById('cancelImplemented');
  const confirmImplemented = document.getElementById('confirmImplemented');
  const cancelDeleteAi = document.getElementById('cancelDeleteAi');
  const confirmDeleteAi = document.getElementById('confirmDeleteAi');
  const cancelDeleteActivity = document.getElementById('cancelDeleteActivity');
  const confirmDeleteActivity = document.getElementById('confirmDeleteActivity');

  // Open AI Analysis Modal
  viewAiBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const projectId = this.dataset.projectId;
      const title = this.dataset.title;
      const status = this.dataset.status;
      const owner = this.dataset.owner;
      const contractor = this.dataset.contractor;
      const completion = this.dataset.completion;
      const progress = this.dataset.progress;
      const variance = this.dataset.variance;
      const risk = this.dataset.risk;
      const confidence = this.dataset.confidence;
      const recommendation = this.dataset.recommendation;

      // Populate modal
      document.getElementById('aiModalProjectId').textContent = projectId;
      document.getElementById('aiModalTitle').textContent = title;
      document.getElementById('aiModalStatus').textContent = status;
      document.getElementById('aiModalOwner').textContent = owner;
      document.getElementById('aiModalContractor').textContent = contractor;
      document.getElementById('aiModalCompletion').textContent = completion;
      document.getElementById('aiModalProgress').textContent = progress;
      document.getElementById('aiModalVariance').textContent = variance;
      document.getElementById('aiModalRisk').textContent = risk;
      document.getElementById('aiModalConfidence').textContent = confidence;
      document.getElementById('aiModalRecommendation').textContent = `"${recommendation}"`;

      // Update status badge color
      const statusBadge = document.getElementById('aiModalStatus');
      statusBadge.className = 'ml-2 inline-block px-3 py-1 rounded-full text-xs font-semibold';
      if (status.toLowerCase().includes('pending')) {
        statusBadge.classList.add('bg-yellow-100', 'text-yellow-700');
      } else if (status.toLowerCase().includes('acknowledge')) {
        statusBadge.classList.add('bg-blue-100', 'text-blue-700');
      } else {
        statusBadge.classList.add('bg-green-100', 'text-green-700');
      }

      aiAnalysisModal.classList.remove('hidden');
      aiAnalysisModal.classList.add('flex');
    });
  });

  // Close AI Analysis Modal
  closeAiAnalysisModal.addEventListener('click', function() {
    aiAnalysisModal.classList.add('hidden');
    aiAnalysisModal.classList.remove('flex');
  });

  // Close modal on outside click
  aiAnalysisModal.addEventListener('click', function(e) {
    if (e.target === aiAnalysisModal) {
      aiAnalysisModal.classList.add('hidden');
      aiAnalysisModal.classList.remove('flex');
    }
  });

  // Open Re-analyze Confirmation Modal
  reanalyzeBtn.addEventListener('click', function() {
    aiAnalysisModal.classList.add('hidden');
    aiAnalysisModal.classList.remove('flex');
    reanalyzeConfirmModal.classList.remove('hidden');
    reanalyzeConfirmModal.classList.add('flex');
  });

  // Cancel Re-analyze
  cancelReanalyze.addEventListener('click', function() {
    reanalyzeConfirmModal.classList.add('hidden');
    reanalyzeConfirmModal.classList.remove('flex');
    aiAnalysisModal.classList.remove('hidden');
    aiAnalysisModal.classList.add('flex');
  });

  // Confirm Re-analyze
  confirmReanalyze.addEventListener('click', function() {
    const loader = this.querySelector('.loader');
    const span = this.querySelector('span:not(.loader)');
    loader.classList.remove('hidden');
    span.textContent = 'Processing...';
    this.disabled = true;

    // Simulate API call
    setTimeout(() => {
      reanalyzeConfirmModal.classList.add('hidden');
      reanalyzeConfirmModal.classList.remove('flex');
      showToast('Project re-analysis triggered successfully!', 'success');
      loader.classList.add('hidden');
      span.textContent = 'Confirm';
      this.disabled = false;
    }, 2000);
  });

  // Open Recommendation Implemented Confirmation Modal
  implementedBtn.addEventListener('click', function() {
    aiAnalysisModal.classList.add('hidden');
    aiAnalysisModal.classList.remove('flex');
    implementedConfirmModal.classList.remove('hidden');
    implementedConfirmModal.classList.add('flex');
  });

  // Cancel Recommendation Implemented
  cancelImplemented.addEventListener('click', function() {
    implementedConfirmModal.classList.add('hidden');
    implementedConfirmModal.classList.remove('flex');
    aiAnalysisModal.classList.remove('hidden');
    aiAnalysisModal.classList.add('flex');
  });

  // Confirm Recommendation Implemented
  confirmImplemented.addEventListener('click', function() {
    const loader = this.querySelector('.loader');
    const span = this.querySelector('span:not(.loader)');
    loader.classList.remove('hidden');
    span.textContent = 'Processing...';
    this.disabled = true;

    // Simulate API call
    setTimeout(() => {
      implementedConfirmModal.classList.add('hidden');
      implementedConfirmModal.classList.remove('flex');
      showToast('Recommendation marked as implemented successfully!', 'success');
      loader.classList.add('hidden');
      span.textContent = 'Confirm';
      this.disabled = false;
    }, 2000);
  });

  // Close confirmation modals on outside click
  reanalyzeConfirmModal.addEventListener('click', function(e) {
    if (e.target === reanalyzeConfirmModal) {
      reanalyzeConfirmModal.classList.add('hidden');
      reanalyzeConfirmModal.classList.remove('flex');
      aiAnalysisModal.classList.remove('hidden');
      aiAnalysisModal.classList.add('flex');
    }
  });

  implementedConfirmModal.addEventListener('click', function(e) {
    if (e.target === implementedConfirmModal) {
      implementedConfirmModal.classList.add('hidden');
      implementedConfirmModal.classList.remove('flex');
      aiAnalysisModal.classList.remove('hidden');
      aiAnalysisModal.classList.add('flex');
    }
  });

  // Open Delete AI Project Modal
  deleteAiBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const projectId = this.dataset.projectId;
      const title = this.dataset.title;
      document.getElementById('deleteProjectInfo').textContent = `${projectId} - ${title}`;
      deleteAiProjectModal.classList.remove('hidden');
      deleteAiProjectModal.classList.add('flex');
    });
  });

  // Cancel Delete AI Project
  cancelDeleteAi.addEventListener('click', function() {
    deleteAiProjectModal.classList.add('hidden');
    deleteAiProjectModal.classList.remove('flex');
  });

  // Confirm Delete AI Project
  confirmDeleteAi.addEventListener('click', function() {
    const loader = this.querySelector('.loader');
    const span = this.querySelector('span:not(.loader)');
    loader.classList.remove('hidden');
    span.textContent = 'Deleting...';
    this.disabled = true;

    // Simulate API call
    setTimeout(() => {
      deleteAiProjectModal.classList.add('hidden');
      deleteAiProjectModal.classList.remove('flex');
      showToast('AI analysis deleted successfully!', 'success');
      loader.classList.add('hidden');
      span.textContent = 'Delete';
      this.disabled = false;
    }, 2000);
  });

  // Close delete modal on outside click
  deleteAiProjectModal.addEventListener('click', function(e) {
    if (e.target === deleteAiProjectModal) {
      deleteAiProjectModal.classList.add('hidden');
      deleteAiProjectModal.classList.remove('flex');
    }
  });

  // Open Delete Activity Modal
  deleteActivityBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const activityDate = this.dataset.activityDate;
      const activityAction = this.dataset.activityAction;
      document.getElementById('deleteActivityInfo').textContent = `${activityDate} - ${activityAction}`;
      deleteActivityModal.classList.remove('hidden');
      deleteActivityModal.classList.add('flex');
    });
  });

  // Cancel Delete Activity
  cancelDeleteActivity.addEventListener('click', function() {
    deleteActivityModal.classList.add('hidden');
    deleteActivityModal.classList.remove('flex');
  });

  // Confirm Delete Activity
  confirmDeleteActivity.addEventListener('click', function() {
    const loader = this.querySelector('.loader');
    const span = this.querySelector('span:not(.loader)');
    loader.classList.remove('hidden');
    span.textContent = 'Deleting...';
    this.disabled = true;

    // Simulate API call
    setTimeout(() => {
      deleteActivityModal.classList.add('hidden');
      deleteActivityModal.classList.remove('flex');
      showToast('Activity record deleted successfully!', 'success');
      loader.classList.add('hidden');
      span.textContent = 'Delete';
      this.disabled = false;
    }, 2000);
  });

  // Close delete activity modal on outside click
  deleteActivityModal.addEventListener('click', function(e) {
    if (e.target === deleteActivityModal) {
      deleteActivityModal.classList.add('hidden');
      deleteActivityModal.classList.remove('flex');
    }
  });

  // ESC key to close modals
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      if (!aiAnalysisModal.classList.contains('hidden')) {
        aiAnalysisModal.classList.add('hidden');
        aiAnalysisModal.classList.remove('flex');
      }
      if (!reanalyzeConfirmModal.classList.contains('hidden')) {
        reanalyzeConfirmModal.classList.add('hidden');
        reanalyzeConfirmModal.classList.remove('flex');
        aiAnalysisModal.classList.remove('hidden');
        aiAnalysisModal.classList.add('flex');
      }
      if (!implementedConfirmModal.classList.contains('hidden')) {
        implementedConfirmModal.classList.add('hidden');
        implementedConfirmModal.classList.remove('flex');
        aiAnalysisModal.classList.remove('hidden');
        aiAnalysisModal.classList.add('flex');
      }
      if (!deleteAiProjectModal.classList.contains('hidden')) {
        deleteAiProjectModal.classList.add('hidden');
        deleteAiProjectModal.classList.remove('flex');
      }
      if (!deleteActivityModal.classList.contains('hidden')) {
        deleteActivityModal.classList.add('hidden');
        deleteActivityModal.classList.remove('flex');
      }
    }
  });

  // Toast notification function
  function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-8 right-8 px-6 py-4 rounded-lg shadow-2xl text-white font-semibold z-50 animate-slide-up ${
      type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.classList.add('opacity-0', 'transition-opacity');
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  // ===================== Charts Initialization (moved from Blade) =====================
  const lineCanvas = document.getElementById('aiRiskLineChart');
  if (lineCanvas && window.Chart) {
    const ctxLine = lineCanvas.getContext('2d');
    new Chart(ctxLine, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
        datasets: [
          {
            label: 'On Track',
            data: [12000, 9000, 11000, 20000, 30000, 22000, 25000],
            borderColor: '#FFD600',
            backgroundColor: 'rgba(255, 214, 0, 0.08)',
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 7,
            pointBackgroundColor: '#FFD600',
            pointBorderColor: '#FFD600',
          },
          {
            label: 'At Risk',
            data: [8000, 12000, 15000, 14000, 18000, 21000, 30000],
            borderColor: '#FFA726',
            backgroundColor: 'rgba(255, 167, 38, 0.08)',
            fill: false,
            borderDash: [6, 6],
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 7,
            pointBackgroundColor: '#FFA726',
            pointBorderColor: '#FFA726',
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            enabled: true,
            backgroundColor: '#222',
            titleColor: '#FFD600',
            bodyColor: '#fff',
            borderColor: '#FFD600',
            borderWidth: 1,
            padding: 12,
            callbacks: {
              label: function(context) {
                return `${context.dataset.label}: ${context.parsed.y.toLocaleString()}`;
              }
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { color: '#fff', font: { weight: 'bold' } }
          },
          y: {
            grid: { color: 'rgba(255,255,255,0.08)' },
            ticks: { color: '#fff', font: { weight: 'bold' } }
          }
        }
      }
    });
  }

  const donutCanvas = document.getElementById('projectStatusDonut');
  if (donutCanvas && window.Chart) {
    const ctxDonut = donutCanvas.getContext('2d');
    new Chart(ctxDonut, {
      type: 'doughnut',
      data: {
        labels: ['On Track', 'At Risk', 'Delayed'],
        datasets: [{
          data: [70, 15, 15],
          backgroundColor: ['#FFD600', '#FFA726', '#0A2342'],
          borderWidth: 0,
        }]
      },
      options: {
        cutout: '70%',
        plugins: {
          legend: { display: false },
          tooltip: {
            enabled: true,
            backgroundColor: '#222',
            titleColor: '#FFD600',
            bodyColor: '#fff',
            borderColor: '#FFD600',
            borderWidth: 1,
            padding: 12,
            callbacks: {
              label: function(context) {
                return `${context.label}: ${context.parsed}%`;
              }
            }
          }
        }
      }
    });
  }
  // =================== End Charts Initialization ===================
});
