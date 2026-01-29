/**
 * Milestone Setup Modal JavaScript
 * Handles 3-step milestone creation with interactive forms
 */

class MilestoneSetupModal {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 3;
        this.projectData = {};
        this.milestones = [];
        this.formData = {
            planName: '',
            paymentMode: 'downpayment',
            startDate: '',
            endDate: '',
            totalBudget: 0,
            downpaymentAmount: 0,
            numberOfMilestones: 5
        };
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupPaymentModeSelection();
    }

    setupEventListeners() {
        // Modal overlay click to close
        const overlay = document.getElementById('milestoneSetupModalOverlay');
        if (overlay) {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    this.closeModal();
                }
            });
        }

        // Back button
        const backBtn = document.getElementById('milestoneBackBtn');
        if (backBtn) {
            backBtn.addEventListener('click', () => this.closeModal());
        }

        // Previous button
        const prevBtn = document.getElementById('milestonePrevBtn');
        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.previousStep());
        }

        // Confirmation modal buttons
        const confirmCancelBtn = document.getElementById('confirmCancelBtn');
        if (confirmCancelBtn) {
            confirmCancelBtn.addEventListener('click', () => this.closeConfirmationModal());
        }

        const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
        if (confirmSubmitBtn) {
            confirmSubmitBtn.addEventListener('click', () => this.confirmAndSubmit());
        }

        // Close confirmation on overlay click
        const confirmOverlay = document.getElementById('milestoneConfirmationOverlay');
        if (confirmOverlay) {
            confirmOverlay.addEventListener('click', (e) => {
                if (e.target === confirmOverlay) {
                    this.closeConfirmationModal();
                }
            });
        }

        // Next button
        const nextBtn = document.getElementById('milestoneNextBtn');
        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.nextStep());
        }

        // Add milestone button
        const addMilestoneBtn = document.getElementById('addMilestoneBtn');
        if (addMilestoneBtn) {
            addMilestoneBtn.addEventListener('click', () => this.addMilestone());
        }

        // Date input listeners
        const startDate = document.getElementById('startDate');
        if (startDate) {
            // Set today as minimum date
            const today = new Date().toISOString().split('T')[0];
            startDate.setAttribute('min', today);
            
            startDate.addEventListener('change', (e) => {
                this.formData.startDate = e.target.value;
                // Set minimum end date to start date
                const endDate = document.getElementById('endDate');
                if (endDate) {
                    endDate.setAttribute('min', e.target.value);
                    // Update the displayed value
                    this.updateDateDisplay(startDate);
                }
            });

            // Also trigger on click to open calendar
            startDate.addEventListener('click', function() {
                this.showPicker?.();
            });
        }

        const endDate = document.getElementById('endDate');
        if (endDate) {
            endDate.addEventListener('change', (e) => {
                this.formData.endDate = e.target.value;
                this.updateDateDisplay(endDate);
            });

            // Also trigger on click to open calendar
            endDate.addEventListener('click', function() {
                this.showPicker?.();
            });
        }

        // Make calendar icons clickable
        document.querySelectorAll('.date-calendar-icon').forEach(icon => {
            icon.addEventListener('click', (e) => {
                const input = e.target.closest('.input-with-icon').querySelector('.date-input');
                if (input) {
                    input.focus();
                    input.showPicker?.();
                }
            });
        });

        // Budget input listeners
        const totalBudget = document.getElementById('totalBudget');
        if (totalBudget) {
            totalBudget.addEventListener('input', (e) => this.formatCurrency(e.target));
        }

        const downpaymentAmount = document.getElementById('downpaymentAmount');
        if (downpaymentAmount) {
            downpaymentAmount.addEventListener('input', (e) => this.formatCurrency(e.target));
        }

        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const confirmOverlay = document.getElementById('milestoneConfirmationOverlay');
                if (confirmOverlay && confirmOverlay.classList.contains('active')) {
                    this.closeConfirmationModal();
                } else if (overlay && overlay.classList.contains('active')) {
                    this.closeModal();
                }
            }
        });
    }

    setupPaymentModeSelection() {
        const paymentModeCards = document.querySelectorAll('.payment-mode-card');
        paymentModeCards.forEach(card => {
            card.addEventListener('click', () => {
                // Remove active class from all cards
                paymentModeCards.forEach(c => c.classList.remove('active'));
                // Add active class to clicked card
                card.classList.add('active');
                // Check the radio input
                const radio = card.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    this.formData.paymentMode = radio.value;
                    this.toggleDownpaymentField();
                }
            });
        });
    }

    toggleDownpaymentField() {
        const downpaymentGroup = document.getElementById('downpaymentGroup');
        if (downpaymentGroup) {
            if (this.formData.paymentMode === 'full_payment') {
                downpaymentGroup.style.display = 'none';
            } else {
                downpaymentGroup.style.display = 'block';
            }
        }
    }

    openModal(projectData = {}) {
        this.projectData = projectData;
        this.currentStep = 1;
        
        // Set project name in header
        const projectNameEl = document.getElementById('milestoneProjectName');
        if (projectNameEl && projectData.title) {
            projectNameEl.textContent = projectData.title;
        }

        // Pre-fill budget if available
        if (projectData.budget) {
            const budgetInput = document.getElementById('totalBudget');
            if (budgetInput) {
                // Extract numbers from budget string (e.g., "₱2.92M" -> 2920000)
                const budgetValue = this.parseBudget(projectData.budget);
                this.formData.totalBudget = budgetValue;
                budgetInput.value = this.formatNumber(budgetValue);
            }
        }

        // Set today as minimum date for start date (already set in setupEventListeners)
        const startDateInput = document.getElementById('startDate');
        if (startDateInput) {
            const today = new Date().toISOString().split('T')[0];
            startDateInput.setAttribute('min', today);
        }

        const endDateInput = document.getElementById('endDate');
        if (endDateInput && this.formData.startDate) {
            endDateInput.setAttribute('min', this.formData.startDate);
        }

        // Show modal
        const overlay = document.getElementById('milestoneSetupModalOverlay');
        if (overlay) {
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        this.updateStepDisplay();
    }

    closeModal() {
        // Close modals
        const overlay = document.getElementById('milestoneSetupModalOverlay');
        if (overlay) {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        const confirmOverlay = document.getElementById('milestoneConfirmationOverlay');
        if (confirmOverlay) {
            confirmOverlay.classList.remove('active');
        }

        // Reset form
        this.resetForm();
    }

    resetForm() {
        this.currentStep = 1;
        this.milestones = [];
        this.formData = {
            planName: '',
            paymentMode: 'downpayment',
            startDate: '',
            endDate: '',
            totalBudget: 0,
            downpaymentAmount: 0,
            numberOfMilestones: 5
        };

        // Reset form inputs
        const form = document.getElementById('milestoneSetupModal');
        if (form) {
            const inputs = form.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                if (input.type === 'radio') {
                    input.checked = input.value === 'downpayment';
                } else {
                    input.value = '';
                }
            });
        }

        // Clear milestones container
        const container = document.getElementById('milestonesContainer');
        if (container) {
            container.innerHTML = '';
        }

        this.updateStepDisplay();
    }

    nextStep() {
        // Validate current step
        if (!this.validateCurrentStep()) {
            return;
        }

        // Save current step data
        this.saveStepData();

        if (this.currentStep === 2) {
            // Generate milestones before going to step 3
            this.generateMilestones();
        }

        if (this.currentStep < this.totalSteps) {
            this.currentStep++;
            this.updateStepDisplay();
        } else {
            // Final step - submit
            this.submitMilestones();
        }
    }

    previousStep() {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.updateStepDisplay();
        }
    }

    updateStepDisplay() {
        // Update step indicators
        const stepItems = document.querySelectorAll('.step-item');
        stepItems.forEach((item, index) => {
            const stepNum = index + 1;
            item.classList.remove('active', 'completed');
            
            if (stepNum === this.currentStep) {
                item.classList.add('active');
            } else if (stepNum < this.currentStep) {
                item.classList.add('completed');
            }
        });

        // Update step content
        const stepContents = document.querySelectorAll('.milestone-step-content');
        stepContents.forEach((content, index) => {
            content.classList.remove('active');
            if (index + 1 === this.currentStep) {
                content.classList.add('active');
            }
        });

        // Update buttons
        const prevBtn = document.getElementById('milestonePrevBtn');
        const nextBtn = document.getElementById('milestoneNextBtn');

        if (prevBtn) {
            prevBtn.disabled = this.currentStep === 1;
            prevBtn.style.display = this.currentStep === 1 ? 'none' : 'flex';
        }

        if (nextBtn) {
            const nextText = nextBtn.querySelector('span');
            if (this.currentStep === this.totalSteps) {
                if (nextText) nextText.textContent = 'Submit';
            } else {
                if (nextText) nextText.textContent = 'Next';
            }
        }
    }

    validateCurrentStep() {
        if (this.currentStep === 1) {
            const planName = document.getElementById('milestonePlanName');
            if (!planName || !planName.value.trim()) {
                this.showNotification('Please enter a milestone plan name', 'error');
                planName?.focus();
                return false;
            }
        }

        if (this.currentStep === 2) {
            const startDate = document.getElementById('startDate');
            if (!startDate || !startDate.value) {
                this.showNotification('Please select a start date', 'error');
                startDate?.focus();
                return false;
            }

            const endDate = document.getElementById('endDate');
            if (!endDate || !endDate.value) {
                this.showNotification('Please select an end date', 'error');
                endDate?.focus();
                return false;
            }

            // Validate end date is after start date
            if (new Date(endDate.value) < new Date(startDate.value)) {
                this.showNotification('End date must be after start date', 'error');
                endDate?.focus();
                return false;
            }

            const totalBudget = document.getElementById('totalBudget');
            if (!totalBudget || !totalBudget.value.trim()) {
                this.showNotification('Please enter the total project cost', 'error');
                totalBudget?.focus();
                return false;
            }

            if (this.formData.paymentMode === 'downpayment') {
                const downpaymentAmount = document.getElementById('downpaymentAmount');
                if (!downpaymentAmount || !downpaymentAmount.value.trim()) {
                    this.showNotification('Please enter the downpayment amount', 'error');
                    downpaymentAmount?.focus();
                    return false;
                }
            }
        }

        if (this.currentStep === 3) {
            const milestoneItems = document.querySelectorAll('.milestone-item');
            
            if (milestoneItems.length === 0) {
                this.showNotification('Please add at least one milestone', 'error');
                return false;
            }

            // Check if total percentage equals 100%
            const totalPercentage = this.updateTotalProgress();
            if (Math.abs(totalPercentage - 100) > 0.5) {
                this.showNotification(`Total percentage must equal 100% (currently ${totalPercentage.toFixed(1)}%)`, 'error');
                return false;
            }

            // Validate all milestone fields
            let hasError = false;
            milestoneItems.forEach((item, index) => {
                const name = item.querySelector('.milestone-name')?.value;
                const percentage = item.querySelector('.milestone-percentage')?.value;
                const targetDate = item.querySelector('.milestone-target-date')?.value;
                const amount = item.querySelector('.milestone-amount')?.value;
                
                if (!name || !name.trim()) {
                    this.showNotification(`Milestone ${index + 1}: Title is required`, 'error');
                    item.querySelector('.milestone-name')?.focus();
                    hasError = true;
                    return false;
                }

                if (!percentage || parseFloat(percentage) <= 0) {
                    this.showNotification(`Milestone ${index + 1}: Percentage is required`, 'error');
                    item.querySelector('.milestone-percentage')?.focus();
                    hasError = true;
                    return false;
                }

                if (!targetDate) {
                    this.showNotification(`Milestone ${index + 1}: Target completion date is required`, 'error');
                    item.querySelector('.milestone-target-date')?.focus();
                    hasError = true;
                    return false;
                }
                
                if (!amount || parseFloat(amount.replace(/,/g, '')) <= 0) {
                    this.showNotification(`Milestone ${index + 1}: Payment amount is required`, 'error');
                    item.querySelector('.milestone-amount')?.focus();
                    hasError = true;
                    return false;
                }
            });

            if (hasError) return false;
        }

        return true;
    }

    saveStepData() {
        if (this.currentStep === 1) {
            const planName = document.getElementById('milestonePlanName');
            this.formData.planName = planName?.value || '';

            const paymentMode = document.querySelector('input[name="paymentMode"]:checked');
            this.formData.paymentMode = paymentMode?.value || 'downpayment';
        }

        if (this.currentStep === 2) {
            const startDate = document.getElementById('startDate');
            this.formData.startDate = startDate?.value || '';

            const endDate = document.getElementById('endDate');
            this.formData.endDate = endDate?.value || '';

            const totalBudget = document.getElementById('totalBudget');
            this.formData.totalBudget = this.parseNumber(totalBudget?.value || '0');

            const downpaymentAmount = document.getElementById('downpaymentAmount');
            this.formData.downpaymentAmount = this.parseNumber(downpaymentAmount?.value || '0');

            // Auto-calculate number of milestones based on project duration if not set
            if (!this.formData.numberOfMilestones && this.formData.startDate && this.formData.endDate) {
                const start = new Date(this.formData.startDate);
                const end = new Date(this.formData.endDate);
                const months = Math.ceil((end - start) / (1000 * 60 * 60 * 24 * 30));
                this.formData.numberOfMilestones = Math.max(3, Math.min(months, 10)); // Between 3-10 milestones
            }
        }

        if (this.currentStep === 3) {
            this.collectMilestoneData();
        }
    }

    generateMilestones() {
        const container = document.getElementById('milestonesContainer');
        if (!container) return;

        container.innerHTML = '';
        
        const remainingBudget = this.formData.totalBudget - this.formData.downpaymentAmount;
        const numMilestones = this.formData.numberOfMilestones || 5;
        const amountPerMilestone = Math.floor(remainingBudget / numMilestones);

        for (let i = 0; i < numMilestones; i++) {
            this.addMilestone(i + 1, amountPerMilestone);
        }
    }

    addMilestone(milestoneNumber = null, defaultAmount = 0, defaultPercentage = 0) {
        const template = document.getElementById('milestoneItemTemplate');
        if (!template) return;

        const container = document.getElementById('milestonesContainer');
        if (!container) return;

        const clone = template.content.cloneNode(true);
        const milestoneItem = clone.querySelector('.milestone-item');
        
        const number = milestoneNumber || container.children.length + 1;
        const milestoneNumberEl = clone.querySelector('.milestone-number');
        if (milestoneNumberEl) {
            milestoneNumberEl.textContent = number;
        }

        // Set default amount if provided
        if (defaultAmount > 0) {
            const amountInput = clone.querySelector('.milestone-amount');
            if (amountInput) {
                amountInput.value = this.formatNumber(defaultAmount);
            }
        }

        // Set default percentage if provided
        if (defaultPercentage > 0) {
            const percentageInput = clone.querySelector('.milestone-percentage');
            if (percentageInput) {
                percentageInput.value = defaultPercentage;
            }
        }

        // Set minimum date for target date
        const targetDateInput = clone.querySelector('.milestone-target-date');
        if (targetDateInput) {
            const startDate = document.getElementById('startDate')?.value;
            if (startDate) {
                targetDateInput.setAttribute('min', startDate);
            }
            
            // Click handler for date input
            targetDateInput.addEventListener('click', function() {
                this.showPicker?.();
            });
        }

        // Click handler for calendar icon
        const calendarIcon = clone.querySelector('.date-calendar-icon');
        if (calendarIcon) {
            calendarIcon.addEventListener('click', (e) => {
                const input = e.target.closest('.input-with-icon').querySelector('.date-input');
                if (input) {
                    input.focus();
                    input.showPicker?.();
                }
            });
        }

        // Remove button handler
        const removeBtn = clone.querySelector('.milestone-remove-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                milestoneItem.remove();
                // Renumber remaining milestones
                const items = container.querySelectorAll('.milestone-item');
                items.forEach((item, index) => {
                    const num = item.querySelector('.milestone-number');
                    if (num) num.textContent = index + 1;
                });
                // Update total progress
                this.updateTotalProgress();
            });
        }

        // Amount input handler
        const amountInput = clone.querySelector('.milestone-amount');
        if (amountInput) {
            amountInput.addEventListener('input', (e) => {
                this.formatCurrency(e.target);
                this.calculateMilestonePercentageFromAmount(milestoneItem);
                this.updateTotalProgress();
            });
        }

        // Percentage input handler
        const percentageInput = clone.querySelector('.milestone-percentage');
        if (percentageInput) {
            percentageInput.addEventListener('input', () => {
                this.calculateMilestoneAmountFromPercentage(milestoneItem);
                this.updateTotalProgress();
            });
        }

        container.appendChild(clone);
        
        // Update total progress after adding
        this.updateTotalProgress();
    }

    collectMilestoneData() {
        this.milestones = [];
        const milestoneItems = document.querySelectorAll('.milestone-item');

        milestoneItems.forEach((item, index) => {
            const name = item.querySelector('.milestone-name')?.value || '';
            const description = item.querySelector('.milestone-description')?.value || '';
            const targetDate = item.querySelector('.milestone-target-date')?.value || '';
            const amountStr = item.querySelector('.milestone-amount')?.value || '0';
            const amount = this.parseNumber(amountStr);
            const percentage = parseFloat(item.querySelector('.milestone-percentage')?.value || '0');

            this.milestones.push({
                order: index + 1,
                name,
                description,
                targetDate,
                amount,
                percentage
            });
        });
    }

    updateTotalProgress() {
        const items = document.querySelectorAll('.milestone-item');
        let totalPercentage = 0;
        
        items.forEach(item => {
            const percentage = parseFloat(item.querySelector('.milestone-percentage')?.value || 0);
            totalPercentage += percentage;
        });
        
        const progressDisplay = document.getElementById('totalProgressValue');
        if (progressDisplay) {
            progressDisplay.textContent = totalPercentage.toFixed(1) + '%';
            
            // Change color based on total
            if (Math.abs(totalPercentage - 100) < 0.5) {
                progressDisplay.style.color = '#10b981'; // Green when exactly 100%
            } else if (totalPercentage > 100) {
                progressDisplay.style.color = '#ef4444'; // Red when over 100%
            } else {
                progressDisplay.style.color = '#EEA24B'; // Orange when under 100%
            }
        }
        
        return totalPercentage;
    }

    formatCurrency(input) {
        let value = input.value.replace(/,/g, '');
        if (value && !isNaN(value)) {
            input.value = this.formatNumber(parseFloat(value));
        }
    }

    formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    parseNumber(str) {
        return parseFloat(str.replace(/,/g, '')) || 0;
    }

    parseBudget(budgetStr) {
        // Parse budget strings like "₱2.92M" or "₱5.5M"
        const cleanStr = budgetStr.replace(/₱|,/g, '').trim();
        if (cleanStr.includes('M')) {
            return parseFloat(cleanStr.replace('M', '')) * 1000000;
        } else if (cleanStr.includes('K')) {
            return parseFloat(cleanStr.replace('K', '')) * 1000;
        }
        return parseFloat(cleanStr) || 0;
    }

    calculateMilestonePercentageFromAmount(milestoneItem) {
        const amount = this.parseNumber(milestoneItem.querySelector('.milestone-amount')?.value || '0');
        const totalBudget = this.formData.totalBudget - this.formData.downpaymentAmount;
        
        if (totalBudget > 0 && amount > 0) {
            const percentage = (amount / totalBudget * 100).toFixed(1);
            const percentageInput = milestoneItem.querySelector('.milestone-percentage');
            if (percentageInput) {
                percentageInput.value = percentage;
            }
        }
    }

    calculateMilestoneAmountFromPercentage(milestoneItem) {
        const percentage = parseFloat(milestoneItem.querySelector('.milestone-percentage')?.value || 0);
        const totalBudget = this.formData.totalBudget - this.formData.downpaymentAmount;
        
        if (totalBudget > 0 && percentage > 0) {
            const amount = totalBudget * (percentage / 100);
            const amountInput = milestoneItem.querySelector('.milestone-amount');
            if (amountInput) {
                amountInput.value = this.formatNumber(amount);
            }
        }
    }

    updateDateDisplay(input) {
        // Format the date display if needed
        if (input && input.value) {
            const date = new Date(input.value + 'T00:00:00');
            const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
            const formatted = date.toLocaleDateString('en-US', options);
            // The input will show it in the browser's locale format automatically
            console.log('Date selected:', formatted);
        }
    }

    submitMilestones() {
        this.saveStepData();
        
        // Show confirmation modal instead of directly submitting
        this.showConfirmationModal();
    }

    showConfirmationModal() {
        const overlay = document.getElementById('milestoneConfirmationOverlay');
        if (!overlay) return;

        // Populate confirmation data
        document.getElementById('confirmPlanName').textContent = this.formData.planName || '-';
        
        const paymentModeText = this.formData.paymentMode === 'downpayment' ? 'Downpayment' : 'Full Payment';
        document.getElementById('confirmPaymentMode').textContent = paymentModeText;
        
        // Format dates
        if (this.formData.startDate && this.formData.endDate) {
            const startDate = new Date(this.formData.startDate).toLocaleDateString('en-US', { 
                year: 'numeric', month: 'short', day: 'numeric' 
            });
            const endDate = new Date(this.formData.endDate).toLocaleDateString('en-US', { 
                year: 'numeric', month: 'short', day: 'numeric' 
            });
            document.getElementById('confirmDuration').textContent = `${startDate} - ${endDate}`;
        }
        
        // Format budget
        document.getElementById('confirmTotalBudget').textContent = `₱${this.formatNumber(this.formData.totalBudget)}`;
        
        // Format downpayment
        if (this.formData.paymentMode === 'downpayment') {
            document.getElementById('confirmDownpayment').textContent = `₱${this.formatNumber(this.formData.downpaymentAmount)}`;
        } else {
            document.getElementById('confirmDownpayment').textContent = 'N/A';
        }
        
        // Milestone count
        document.getElementById('confirmMilestoneCount').textContent = `${this.milestones.length} milestone${this.milestones.length !== 1 ? 's' : ''}`;
        
        // Show overlay
        overlay.classList.add('active');
    }

    closeConfirmationModal() {
        const overlay = document.getElementById('milestoneConfirmationOverlay');
        if (overlay) {
            overlay.classList.remove('active');
        }
    }

    confirmAndSubmit() {
        // Prepare final data
        const finalData = {
            project: this.projectData,
            planName: this.formData.planName,
            paymentMode: this.formData.paymentMode,
            startDate: this.formData.startDate,
            endDate: this.formData.endDate,
            totalBudget: this.formData.totalBudget,
            downpaymentAmount: this.formData.downpaymentAmount,
            milestones: this.milestones
        };

        console.log('Milestone Setup Data:', finalData);

        // Close confirmation modal
        this.closeConfirmationModal();

        // Show success notification
        this.showNotification('Milestone setup created successfully!', 'success');

        // Close main modal
        setTimeout(() => {
            this.closeModal();
            // TODO: Send data to backend API
            // this.saveMilestonesToBackend(finalData);
        }, 1000);
    }

    showNotification(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `milestone-toast milestone-toast-${type}`;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            color: white;
            font-weight: 500;
            z-index: 10000;
            animation: slideInUp 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        `;

        if (type === 'success') {
            toast.style.background = 'linear-gradient(135deg, #10B981 0%, #059669 100%)';
        } else if (type === 'error') {
            toast.style.background = 'linear-gradient(135deg, #EF4444 0%, #DC2626 100%)';
        } else {
            toast.style.background = 'linear-gradient(135deg, #EEA24B 0%, #E89A3C 100%)';
        }

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOutDown 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// Initialize and expose globally
let milestoneSetupModal;

document.addEventListener('DOMContentLoaded', () => {
    milestoneSetupModal = new MilestoneSetupModal();
});

// Export function to open modal
window.openMilestoneSetupModal = (projectData) => {
    if (milestoneSetupModal) {
        milestoneSetupModal.openModal(projectData);
    }
};
