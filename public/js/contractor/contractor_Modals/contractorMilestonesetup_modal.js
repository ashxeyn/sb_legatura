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
            numberOfMilestones: 1,
            milestoneId: null
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
            startDate.addEventListener('click', function () {
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
            endDate.addEventListener('click', function () {
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
        if (projectData.proposed_cost) {
            const budgetInput = document.getElementById('totalBudget');
            if (budgetInput) {
                const budgetValue = parseFloat(projectData.proposed_cost);
                this.formData.totalBudget = budgetValue;
                budgetInput.value = this.formatNumber(budgetValue);
            }
        } else if (projectData.budget) {
            const budgetInput = document.getElementById('totalBudget');
            if (budgetInput) {
                const budgetValue = this.parseBudget(projectData.budget);
                this.formData.totalBudget = budgetValue;
                budgetInput.value = this.formatNumber(budgetValue);
            }
        }

        // Show modal
        const overlay = document.getElementById('milestoneSetupModalOverlay');
        if (overlay) {
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        this.updateStepDisplay();
    }

    /**
     * Open modal in Edit Mode with pre-populated data
     */
    openEditModal(milestoneData = {}, itemsData = {}) {
        this.resetForm();
        this.projectData = milestoneData;
        this.milestoneItemsData = itemsData;

        // Populate Form Data
        this.formData.milestoneId = milestoneData.id;
        this.formData.planName = milestoneData.title;
        this.formData.paymentMode = milestoneData.payment_mode;
        this.formData.startDate = milestoneData.start_date;
        this.formData.endDate = milestoneData.end_date;
        this.formData.totalBudget = parseFloat(milestoneData.proposed_cost) || 0;
        this.formData.downpaymentAmount = parseFloat(milestoneData.downpayment_amount) || 0;

        // Set Step 1 UI
        const planNameInput = document.getElementById('milestonePlanName');
        if (planNameInput) planNameInput.value = this.formData.planName;

        const paymentModeCards = document.querySelectorAll('.payment-mode-card');
        paymentModeCards.forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            if (radio && radio.value === this.formData.paymentMode) {
                radio.checked = true;
                card.classList.add('active');
            } else {
                card.classList.remove('active');
            }
        });
        this.toggleDownpaymentField();

        // Set Step 2 UI
        const startDateInput = document.getElementById('startDate');
        if (startDateInput) startDateInput.value = this.formData.startDate;

        const endDateInput = document.getElementById('endDate');
        if (endDateInput) {
            endDateInput.value = this.formData.endDate;
            endDateInput.setAttribute('min', this.formData.startDate);
        }

        const totalBudgetInput = document.getElementById('totalBudget');
        if (totalBudgetInput) totalBudgetInput.value = this.formatNumber(this.formData.totalBudget);

        const downpaymentInput = document.getElementById('downpaymentAmount');
        if (downpaymentInput) downpaymentInput.value = this.formatNumber(this.formData.downpaymentAmount);

        // Pre-set number of milestones for Step 3
        const items = Object.values(itemsData);
        this.formData.numberOfMilestones = items.length || 1;

        // Set project name in header
        const projectNameEl = document.getElementById('milestoneProjectName');
        if (projectNameEl && milestoneData.title) {
            projectNameEl.textContent = milestoneData.title;
        }

        // Show modal
        const overlay = document.getElementById('milestoneSetupModalOverlay');
        if (overlay) {
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        this.currentStep = 1;
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

        const paymentModeInputs = document.querySelectorAll('input[name="paymentMode"]');
        paymentModeInputs.forEach(input => {
            input.checked = false;
            input.closest('.payment-mode-card').classList.remove('active');
        });

        // Reset downpayment group visibility
        const downpaymentGroup = document.getElementById('downpaymentGroup');
        if (downpaymentGroup) {
            downpaymentGroup.style.display = 'block'; // Default to show, or hidden until selection?
        }

        this.formData = {
            planName: '',
            paymentMode: null, // Clear payment mode
            startDate: '',
            endDate: '',
            totalBudget: 0,
            downpaymentAmount: 0,
            numberOfMilestones: 1,
            milestoneId: null
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

    async nextStep() {
        // Validate current step
        if (!this.validateCurrentStep()) {
            return;
        }

        // Save current step data
        this.saveStepData();

        // AJAX validation for Step 1
        if (this.currentStep === 1) {
            const isValid = await this.validateStep1WithBackend();
            if (!isValid) {
                return; // Block transition if backend validation fails
            }
        }

        // AJAX validation for Step 2
        if (this.currentStep === 2) {
            // Check budget warning first
            if (this.checkBudgetWarning()) {
                return; // Stop here, warning modal is shown
            }

            const isValid = await this.validateStep2WithBackend();
            if (!isValid) {
                return; // Block transition if backend validation fails
            }
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
        this.clearAllInlineErrors();
        let isValid = true;

        if (this.currentStep === 1) {
            const planName = document.getElementById('milestonePlanName');
            if (!planName || !planName.value.trim()) {
                this.showInlineError(planName, 'This field is required');
                planName?.focus();
                return false;
            }
        }

        if (this.currentStep === 2) {
            const startDate = document.getElementById('startDate');
            if (!startDate || !startDate.value) {
                this.showInlineError(startDate, 'This field is required');
                startDate?.focus();
                isValid = false;
            }

            const endDate = document.getElementById('endDate');
            if (!endDate || !endDate.value) {
                this.showInlineError(endDate, 'This field is required');
                if (isValid) endDate?.focus();
                isValid = false;
            }

            // Validate end date is after start date
            if (startDate && startDate.value && endDate && endDate.value) {
                if (new Date(endDate.value) < new Date(startDate.value)) {
                    this.showInlineError(endDate, 'End date must be after start date');
                    if (isValid) endDate?.focus();
                    isValid = false;
                }
            }

            const totalBudget = document.getElementById('totalBudget');
            if (!totalBudget || !totalBudget.value.trim()) {
                this.showInlineError(totalBudget, 'This field is required');
                if (isValid) totalBudget?.focus();
                isValid = false;
            }

            if (this.formData.paymentMode === 'downpayment') {
                const downpaymentAmount = document.getElementById('downpaymentAmount');
                if (!downpaymentAmount || !downpaymentAmount.value.trim()) {
                    this.showInlineError(downpaymentAmount, 'This field is required');
                    if (isValid) downpaymentAmount?.focus();
                    isValid = false;
                }
            }

            if (!isValid) return false;
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
                const nameInput = item.querySelector('.milestone-name');
                const percentageInput = item.querySelector('.milestone-percentage');
                const targetDateInput = item.querySelector('.milestone-target-date');
                // Amount is readonly/auto-calculated, so skipping required check for user input,
                // but we can check if it's valid if needed. Usually valid if percentage is valid.

                const name = nameInput?.value;
                const percentage = percentageInput?.value;
                const targetDate = targetDateInput?.value;

                if (!name || !name.trim()) {
                    this.showInlineError(nameInput, 'Title is required');
                    if (!hasError) nameInput?.focus();
                    hasError = true;
                }

                if (!percentage || parseFloat(percentage) <= 0) {
                    this.showInlineError(percentageInput, 'Required');
                    if (!hasError) percentageInput?.focus();
                    hasError = true;
                }

                if (!targetDate) {
                    this.showInlineError(targetDateInput, 'Date is required');
                    if (!hasError) targetDateInput?.focus();
                    hasError = true;
                }

                // Chronological Date Validation
                // Check if current date is after previous milestone date
                if (index > 0 && targetDate && !hasError) {
                    const prevItem = milestoneItems[index - 1];
                    const prevDateInput = prevItem.querySelector('.milestone-target-date');
                    const prevDateVal = prevDateInput?.value;

                    if (prevDateVal && targetDate <= prevDateVal) {
                        this.showInlineError(targetDateInput, `Date must be after previous milestone (${this.formatDateForDisplay(prevDateVal)})`);
                        if (!hasError) targetDateInput?.focus();
                        hasError = true;
                    }
                }
            });

            // Validate Last Milestone Date Rule
            if (milestoneItems.length > 0) {
                const lastItem = milestoneItems[milestoneItems.length - 1];
                const lastDateInput = lastItem.querySelector('.milestone-target-date');
                const projectEndDate = this.formData.endDate;

                if (lastDateInput && lastDateInput.value && projectEndDate) {
                    if (lastDateInput.value !== projectEndDate) {
                        this.showInlineError(lastDateInput, `Last milestone date must be ${this.formatDateForDisplay(projectEndDate)}`);
                        if (!hasError) lastDateInput.focus();
                        hasError = true;
                    }
                }
            }

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

            // Default to 1 milestone as requested
            if (!this.formData.numberOfMilestones) {
                this.formData.numberOfMilestones = 1;
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

        if (this.formData.milestoneId && this.milestoneItemsData) {
            // Edit mode: use existing items
            const items = Object.values(this.milestoneItemsData).sort((a, b) => (a.sequenceNumber || 0) - (b.sequenceNumber || 0));
            items.forEach((item, index) => {
                this.addMilestone(index + 1, item.cost, item.percentage, item);
            });
        } else {
            // New mode: generate empty ones
            const numMilestones = this.formData.numberOfMilestones || 1;
            for (let i = 0; i < numMilestones; i++) {
                this.addMilestone(i + 1, 0, 0);
            }
        }
    }

    addMilestone(milestoneNumber = null, defaultAmount = 0, defaultPercentage = 0, itemData = null) {
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

        // Set existing item data if provided
        if (itemData) {
            const nameInput = clone.querySelector('.milestone-name');
            if (nameInput) nameInput.value = itemData.title;

            const descInput = clone.querySelector('.milestone-description');
            if (descInput) descInput.value = itemData.description;

            const targetDateInput = clone.querySelector('.milestone-target-date');
            if (targetDateInput) targetDateInput.value = itemData.date;
        }

        // Set minimum date for target date
        const targetDateInput = clone.querySelector('.milestone-target-date');
        if (targetDateInput) {
            const startDate = document.getElementById('startDate')?.value;
            if (startDate) {
                targetDateInput.setAttribute('min', startDate);
            }

            // Click handler for date input
            targetDateInput.addEventListener('click', function () {
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

        if (totalBudget > 0) {
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

    async confirmAndSubmit() {
        const confirmBtn = document.getElementById('confirmSubmitBtn');
        const originalContent = confirmBtn.innerHTML;

        try {
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

            // Show loading state
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fi fi-rr-spinner spinner-rotate"></i> <span>Submitting...</span>';

            // Submit to backend
            const success = await this.submitMilestonesToBackend();

            if (success) {
                // Close confirmation modal only on success
                this.closeConfirmationModal();
            } else {
                // Reset button on failure
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalContent;
            }
        } catch (error) {
            console.error('Submission error:', error);
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalContent;
        }
    }

    /**
     * Get CSRF token from multiple possible sources
     */
    getCsrfToken() {
        // Try meta tag first
        let token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Fallback to window.Laravel if available
        if (!token && window.Laravel && window.Laravel.csrfToken) {
            token = window.Laravel.csrfToken;
        }

        // Fallback to hidden input field
        if (!token) {
            const hiddenInput = document.querySelector('input[name="_token"]');
            token = hiddenInput?.value;
        }

        return token;
    }

    /**
     * Validate Step 1 with backend
     */
    async validateStep1WithBackend() {
        try {
            const csrfToken = this.getCsrfToken();

            const response = await fetch('/contractor/milestone/setup/step1', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    project_id: this.projectData.project_id || this.projectData.id,
                    milestone_name: this.formData.planName,
                    milestone_description: this.formData.planName,
                    payment_mode: this.formData.paymentMode,
                    milestone_id: this.formData.milestoneId
                })
            });

            const data = await response.json();

            if (response.status === 422) {
                // Validation errors
                const errors = data.errors || {};

                if (errors.milestone_name) {
                    this.showInlineError(document.getElementById('milestonePlanName'), errors.milestone_name[0]);
                }

                return false;
            }

            if (!response.ok || !data.success) {
                // Determine if errors is an array to display properly
                let errorMsg = 'Failed to validate step 1';
                if (data.errors && Array.isArray(data.errors) && data.errors.length > 0) {
                    errorMsg = data.errors[0];
                } else if (data.message) {
                    errorMsg = data.message;
                }

                this.showNotification(errorMsg, 'error');
                return false;
            }

            return true;
        } catch (error) {
            console.error('Step 1 validation error:', error);
            this.showNotification('Network error. Please try again.', 'error');
            return false;
        }
    }


    /**
     * Validate Step 2 with backend
     */
    async validateStep2WithBackend() {
        try {
            const csrfToken = this.getCsrfToken();

            const response = await fetch('/contractor/milestone/setup/step2', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    start_date: this.formData.startDate,
                    end_date: this.formData.endDate,
                    total_project_cost: this.formData.totalBudget,
                    downpayment_amount: this.formData.paymentMode === 'downpayment' ? this.formData.downpaymentAmount : 0
                })
            });

            const data = await response.json();

            if (response.status === 422) {
                const errors = data.errors || {};

                if (errors.start_date) this.showInlineError(document.getElementById('startDate'), errors.start_date[0]);
                if (errors.end_date) this.showInlineError(document.getElementById('endDate'), errors.end_date[0]);
                if (errors.total_project_cost) this.showInlineError(document.getElementById('totalBudget'), errors.total_project_cost[0]);
                if (errors.downpayment_amount) this.showInlineError(document.getElementById('downpaymentAmount'), errors.downpayment_amount[0]);

                return false;
            }

            if (!response.ok || !data.success) {
                this.showNotification(data.message || 'Failed to validate step 2', 'error');
                return false;
            }

            return true;
        } catch (error) {
            console.error('Step 2 validation error:', error);
            this.showNotification('Network error. Please try again.', 'error');
            return false;
        }
    }

    /**
     * Submit all milestone data to backend
     */
    async submitMilestonesToBackend() {
        try {
            const csrfToken = this.getCsrfToken();

            // Prepare milestone items array
            const items = this.milestones.map(milestone => ({
                title: milestone.name,
                description: milestone.description || '',
                percentage: milestone.percentage,
                date_to_finish: milestone.targetDate,
                amount: milestone.amount
            }));

            const response = await fetch('/contractor/milestone/setup/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    items: JSON.stringify(items)
                })
            });

            const data = await response.json();

            if (response.status === 422) {
                // Validation errors
                const errors = data.errors || {};
                const errorMessages = Object.values(errors).flat();
                this.showNotification(errorMessages[0] || 'Validation failed', 'error');
                return false;
            }

            if (response.status === 500) {
                // Server error
                this.showNotification(data.message || 'Server error occurred', 'error');
                return false;
            }

            if (!response.ok || !data.success) {
                this.showNotification(data.message || 'Failed to submit milestone', 'error');
                return false;
            }

            // Success!
            this.showNotification(data.message || 'Milestone setup created successfully!', 'success');

            // Close main modal immediately
            this.closeModal();

            // Redirect if URL provided
            if (data.redirect_url) {
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 1500);
            } else {
                // Reload page after a delay to show toast
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }

            return true;
        } catch (error) {
            console.error('Milestone submission error:', error);
            this.showNotification('Network error. Please try again.', 'error');
            return false;
        }
    }

    showNotification(message, type = 'info') {
        try {
            const toast = document.createElement('div');
            toast.className = 'site-toast site-toast-' + type;
            toast.style.position = 'fixed';
            toast.style.right = '20px';
            toast.style.top = '20px';
            toast.style.zIndex = '11000';
            toast.style.padding = '12px 16px';
            toast.style.borderRadius = '8px';
            toast.style.boxShadow = '0 2px 10px rgba(0,0,0,0.12)';

            // Set background color based on type
            let bgColor = '#374151'; // Default info/dark
            if (type === 'success') bgColor = '#16a34a'; // Green
            if (type === 'error') bgColor = '#ef4444'; // Red
            if (type === 'warning') bgColor = '#f59e0b'; // Orange

            toast.style.background = bgColor;
            toast.style.color = '#fff';
            toast.textContent = message;

            // Initial animation state
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-8px)';
            toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

            document.body.appendChild(toast);

            // Trigger enter animation
            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            });

            // Trigger exit animation and remove
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-8px)';
                setTimeout(() => toast.remove(), 350);
            }, 3000); // Wait 3 seconds before hiding
        } catch (e) {
            console.error('showNotification failed', e);
        }
    }
    /**
     * Show inline error message
     */
    showInlineError(input, message) {
        if (!input) return;
        const formGroup = input.closest('.form-group');
        const errorSpan = formGroup?.querySelector('.validation-error');
        if (errorSpan) {
            errorSpan.textContent = message;
            errorSpan.style.display = 'block';
            input.style.borderColor = '#ef4444';
            input.classList.add('border-red-500');
        }
    }

    /**
     * Clear inline error message
     */
    clearInlineError(input) {
        if (!input) return;
        const formGroup = input.closest('.form-group');
        const errorSpan = formGroup?.querySelector('.validation-error');
        if (errorSpan) {
            errorSpan.style.display = 'none';
            input.style.borderColor = '';
            input.classList.remove('border-red-500');
        }
    }

    /**
     * Clear all inline errors
     */
    clearAllInlineErrors() {
        const errorSpans = document.querySelectorAll('.validation-error');
        errorSpans.forEach(span => {
            span.style.display = 'none';
            const formGroup = span.closest('.form-group');
            const input = formGroup?.querySelector('.form-input, .form-textarea');
            if (input) {
                input.style.borderColor = '';
                input.classList.remove('border-red-500');
            }
        });
    }

    /**
     * Format date for display (e.g. Feb 23, 2027)
     */
    formatDateForDisplay(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    /**
     * Check if total project cost is within project budget range
     * Returns true if warning is shown, false otherwise
     */
    checkBudgetWarning() {
        // Get project Min/Max from raw data (from contractor_Myprojects.js) or fallback
        const raw = this.projectData.raw || this.projectData;
        let minBudget = 0;
        let maxBudget = 0;

        if (raw.budget_range_min) {
            minBudget = parseFloat(raw.budget_range_min);
        }
        if (raw.budget_range_max) {
            maxBudget = parseFloat(raw.budget_range_max);
        }

        // Get entered total cost - ensure it's up to date
        const totalBudgetInput = document.getElementById('totalBudget');
        const totalProjectCost = this.parseNumber(totalBudgetInput?.value || '0');

        // Skip if no range defined or both are 0
        if (!minBudget && !maxBudget) return false;

        let warningMessage = '';
        let warningType = 'warning';

        // Check Min Range
        if (minBudget > 0 && totalProjectCost < minBudget) {
            warningMessage = `The total project cost you entered (<span class="font-semibold text-gray-900">₱${this.formatNumber(totalProjectCost)}</span>) is <strong>lower</strong> than the project's minimum budget range (<span class="font-semibold text-gray-900">₱${this.formatNumber(minBudget)}</span>).`;
        }
        // Check Max Range
        else if (maxBudget > 0 && totalProjectCost > maxBudget) {
            warningMessage = `The total project cost you entered (<span class="font-semibold text-gray-900">₱${this.formatNumber(totalProjectCost)}</span>) is <strong>higher</strong> than the project's maximum budget range (<span class="font-semibold text-gray-900">₱${this.formatNumber(maxBudget)}</span>).`;
        }

        if (warningMessage) {
            this.showBudgetWarning(warningMessage, warningType);
            return true;
        }

        return false;
    }

    /**
     * Show Budget Warning Modal
     */
    showBudgetWarning(message, type) {
        const modal = document.getElementById('budgetWarningModal');
        const overlay = document.getElementById('budgetWarningOverlay');
        const iconContainer = document.getElementById('budgetWarningIcon');
        const msgEl = document.getElementById('budgetWarningMessage');
        const editBtn = document.getElementById('budgetWarningEditBtn');
        const continueBtn = document.getElementById('budgetWarningContinueBtn');

        if (modal && msgEl) {
            msgEl.innerHTML = message;

            // Setup Icon
            iconContainer.className = 'modal-icon-container ' + type;

            // Show Modal
            modal.classList.remove('hidden');

            // Define close handler
            const closeModal = () => {
                modal.classList.add('hidden');
            };

            // Bind actions
            editBtn.onclick = () => {
                closeModal();
                // Focus on total budget input
                setTimeout(() => {
                    const input = document.getElementById('totalBudget');
                    input?.focus();
                    input?.select();
                }, 100);
            };

            continueBtn.onclick = async () => {
                closeModal();
                // Proceed with Step 2 validation explicitly
                // We need to show loading state perhaps? separate from nextStep
                // But validateStep2WithBackend handles UI blocking usually via class methods?
                // Actually validateStep2WithBackend calls fetch and shows notification on error.
                // It returns isValid boolean.

                // If valid, we need to proceed to generate milestones and move to step 3.
                // We should reuse the logic from nextStep() for step 2:

                const isValid = await this.validateStep2WithBackend();
                if (isValid) {
                    this.generateMilestones();
                    this.currentStep++;
                    this.updateStepDisplay();
                }
            };

            overlay.onclick = closeModal;
        }
    }
}

// Initialize and expose globally
let milestoneSetupModal;

document.addEventListener('DOMContentLoaded', () => {
    milestoneSetupModal = new MilestoneSetupModal();
    window.milestoneSetupModal = milestoneSetupModal;
});

// Export function to open modal
window.openMilestoneSetupModal = (projectData) => {
    if (milestoneSetupModal) {
        milestoneSetupModal.openModal(projectData);
    }
};
