<!-- Milestone Setup Modal -->
<div class="milestone-setup-modal-overlay" id="milestoneSetupModalOverlay">
    <div class="milestone-setup-modal" id="milestoneSetupModal">
        <!-- Modal Header -->
        <div class="milestone-modal-header">
            <button type="button" class="milestone-back-btn" id="milestoneBackBtn">
                <i class="fi fi-rr-arrow-left"></i>
            </button>
            <div class="milestone-header-content">
                <h2 class="milestone-modal-title">Setup Milestones</h2>
                <p class="milestone-project-name" id="milestoneProjectName">Modern 2-Story Residential House</p>
            </div>
        </div>

        <!-- Step Indicators --> 
        <div class="milestone-steps-indicator">
            <div class="step-item active" data-step="1">
                <div class="step-circle">
                    <span class="step-number">1</span>
                </div>
                <span class="step-label">Basic Info</span>
            </div>
            <div class="step-connector"></div>
            <div class="step-item" data-step="2">
                <div class="step-circle">
                    <span class="step-number">2</span>
                </div>
                <span class="step-label">Payment Details</span>
            </div>
            <div class="step-connector"></div>
            <div class="step-item" data-step="3">
                <div class="step-circle">
                    <span class="step-number">3</span>
                </div>
                <span class="step-label">Milestones</span>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="milestone-modal-body">
            <!-- Step 1: Basic Information -->
            <div class="milestone-step-content active" id="step1Content">
                <h3 class="step-content-title">Basic Information</h3>
                <p class="step-content-description">Enter the milestone plan name and select the payment mode for this project.</p>

                <div class="form-group">
                    <label class="form-label" for="milestonePlanName">
                        Milestone Plan Name <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-input" 
                        id="milestonePlanName" 
                        placeholder="e.g., Phase 1 - Foundation Work"
                        required
                    >
                    <span class="validation-error" id="error-milestonePlanName" style="color: #ef4444; font-size: 12px; display: none; margin-top: 4px;">This field is required</span>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Payment Mode <span class="required">*</span>
                    </label>
                    <div class="payment-mode-options">
                        <label class="payment-mode-card">
                            <input type="radio" name="paymentMode" value="downpayment">
                            <div class="payment-mode-icon">
                                <i class="fi fi-rr-money-check-edit"></i>
                            </div>
                            <div class="payment-mode-content">
                                <h4 class="payment-mode-title">Downpayment</h4>
                                <p class="payment-mode-description">Owner pays initial downpayment, then milestone-based payments</p>
                            </div>
                        </label>

                        <label class="payment-mode-card">
                            <input type="radio" name="paymentMode" value="full_payment">
                            <div class="payment-mode-icon">
                                <i class="fi fi-rr-wallet"></i>
                            </div>
                            <div class="payment-mode-content">
                                <h4 class="payment-mode-title">Full Payment</h4>
                                <p class="payment-mode-description">Owner pays full amount upon project completion</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Step 2: Payment Details -->
            <div class="milestone-step-content" id="step2Content">
                <h3 class="step-content-title">Payment Details</h3>
                <p class="step-content-description">Set the project timeline and financial details.</p>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="startDate">
                            Start Date <span class="required">*</span>
                        </label>
                        <div class="input-with-icon date-field">
                            <i class="fi fi-rr-calendar input-icon-left"></i>
                            <input 
                                type="date" 
                                class="form-input date-input" 
                                id="startDate"
                                required
                            >
                            <i class="fi fi-rr-calendar date-calendar-icon"></i>
                        </div>
                        <span class="validation-error" id="error-startDate" style="color: #ef4444; font-size: 12px; display: none; margin-top: 4px;">This field is required</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="endDate">
                            End Date <span class="required">*</span>
                        </label>
                        <div class="input-with-icon date-field">
                            <i class="fi fi-rr-calendar input-icon-left"></i>
                            <input 
                                type="date" 
                                class="form-input date-input" 
                                id="endDate"
                                required
                            >
                            <i class="fi fi-rr-calendar date-calendar-icon"></i>
                        </div>
                        <span class="validation-error" id="error-endDate" style="color: #ef4444; font-size: 12px; display: none; margin-top: 4px;">This field is required</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="totalBudget">
                        Total Project Cost (₱) <span class="required">*</span>
                    </label>
                    <div class="input-with-icon">
                        <i class="fi fi-rr-peso input-icon-left"></i>
                        <input 
                            type="text" 
                            class="form-input" 
                            id="totalBudget" 
                            placeholder="0.00"
                            required
                        >
                    </div>
                    <span class="validation-error" id="error-totalBudget" style="color: #ef4444; font-size: 12px; display: none; margin-top: 4px;">This field is required</span>
                </div>

                <div class="form-group" id="downpaymentGroup">
                    <label class="form-label" for="downpaymentAmount">
                        Downpayment Amount (₱) <span class="required">*</span>
                    </label>
                    <div class="input-with-icon">
                        <i class="fi fi-rr-peso input-icon-left"></i>
                        <input 
                            type="text" 
                            class="form-input" 
                            id="downpaymentAmount" 
                            placeholder="0.00"
                        >
                    </div>
                    <span class="validation-error" id="error-downpaymentAmount" style="color: #ef4444; font-size: 12px; display: none; margin-top: 4px;">This field is required</span>
                </div>
            </div>

            <!-- Step 3: Milestones -->
            <div class="milestone-step-content" id="step3Content">
                <h3 class="step-content-title">Milestone Items</h3>
                <p class="step-content-description">Break down the project into milestones. Total percentage must equal 100%.</p>

                <div class="total-progress-display">
                    <span class="total-progress-label">Total Progress:</span>
                    <span class="total-progress-value" id="totalProgressValue">0.0%</span>
                </div>

                <div id="milestonesContainer">
                    <!-- Milestone items will be dynamically added here -->
                </div>

                <button type="button" class="add-milestone-btn" id="addMilestoneBtn">
                    <i class="fi fi-rr-plus"></i>
                    <span>Add Another Milestone</span>
                </button>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="milestone-modal-footer">
            <button type="button" class="btn-secondary" id="milestonePrevBtn">
                <i class="fi fi-rr-arrow-left"></i>
                <span>Previous</span>
            </button>
            <button type="button" class="btn-primary" id="milestoneNextBtn">
                <span>Next</span>
                <i class="fi fi-rr-arrow-right"></i>
            </button>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="milestone-confirmation-overlay" id="milestoneConfirmationOverlay">
    <div class="milestone-confirmation-modal">
        <div class="confirmation-header">
            <div class="confirmation-icon">
                <i class="fi fi-rr-interrogation"></i>
            </div>
            <h3 class="confirmation-title">Confirm Milestone Setup</h3>
            <p class="confirmation-subtitle">Please review your milestone plan before submitting</p>
        </div>

        <div class="confirmation-body">
            <div class="confirmation-section">
                <h4 class="confirmation-section-title">Plan Details</h4>
                <div class="confirmation-detail">
                    <span class="detail-label">Plan Name:</span>
                    <span class="detail-value" id="confirmPlanName">-</span>
                </div>
                <div class="confirmation-detail">
                    <span class="detail-label">Payment Mode:</span>
                    <span class="detail-value" id="confirmPaymentMode">-</span>
                </div>
                <div class="confirmation-detail">
                    <span class="detail-label">Project Duration:</span>
                    <span class="detail-value" id="confirmDuration">-</span>
                </div>
            </div>

            <div class="confirmation-section">
                <h4 class="confirmation-section-title">Financial Summary</h4>
                <div class="confirmation-detail">
                    <span class="detail-label">Total Budget:</span>
                    <span class="detail-value" id="confirmTotalBudget">-</span>
                </div>
                <div class="confirmation-detail">
                    <span class="detail-label">Downpayment:</span>
                    <span class="detail-value" id="confirmDownpayment">-</span>
                </div>
                <div class="confirmation-detail highlight">
                    <span class="detail-label">Total Milestones:</span>
                    <span class="detail-value" id="confirmMilestoneCount">-</span>
                </div>
            </div>

            <div class="confirmation-warning">
                <i class="fi fi-rr-info"></i>
                <span>Once submitted, this milestone plan will be sent to the property owner for review.</span>
            </div>
        </div>

        <div class="confirmation-footer">
            <button type="button" class="btn-cancel" id="confirmCancelBtn">
                <i class="fi fi-rr-cross"></i>
                <span>Cancel</span>
            </button>
            <button type="button" class="btn-confirm" id="confirmSubmitBtn">
                <i class="fi fi-rr-check"></i>
                <span>Confirm & Submit</span>
            </button>
        </div>
    </div>
</div>

<!-- Milestone Item Template -->
<template id="milestoneItemTemplate">
    <div class="milestone-item">
        <div class="milestone-item-header">
            <h4 class="milestone-item-title">Milestone <span class="milestone-number"></span></h4>
            <button type="button" class="milestone-remove-btn">
                <i class="fi fi-rr-trash"></i>
            </button>
        </div>
        <div class="milestone-item-body">
            <div class="milestone-row-fields">
                <div class="form-group">
                    <label class="form-label">
                        Percentage <span class="required">*</span>
                    </label>
                    <div class="input-with-suffix">
                        <input 
                            type="number" 
                            class="form-input milestone-percentage" 
                            placeholder="0"
                            min="0"
                            max="100"
                            step="0.1"
                            required
                        >
                        <span class="input-suffix">%</span>
                    </div>
                    <span class="validation-error error-milestone-percentage" style="color: #ef4444; font-size: 12px; display: none; margin-top: 4px;"></span>
                </div>
                <div class="form-group flex-1">
                    <label class="form-label">
                        Title <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-input milestone-name" 
                        placeholder="e.g., Foundation Co"
                        required
                    >
                    <span class="validation-error error-milestone-name" style="color: #ef4444; font-size: 12px; display: none; margin-top: 4px;"></span>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">
                    Description
                </label>
                <textarea 
                    class="form-textarea milestone-description" 
                    placeholder="Describe the milestone requirements..."
                    rows="3"
                ></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">
                    Target Completion Date <span class="required">*</span>
                </label>
                <div class="input-with-icon date-field">
                    <i class="fi fi-rr-calendar input-icon-left"></i>
                    <input 
                        type="date" 
                        class="form-input date-input milestone-target-date" 
                        required
                    >
                    <i class="fi fi-rr-calendar date-calendar-icon"></i>
                </div>
                <span class="validation-error error-milestone-target-date" style="color: #ef4444; font-size: 12px; display: none; margin-top: 4px;"></span>
            </div>
            <div class="form-group">
                <label class="form-label">
                    Payment Amount <span class="required">*</span>
                </label>
                <div class="input-with-icon">
                    <i class="fi fi-rr-peso input-icon-left"></i>
                    <input 
                        type="text" 
                        class="form-input milestone-amount" 
                        placeholder="0.00"
                        readonly
                        style="background-color: #f3f4f6; cursor: not-allowed;"
                    >
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Generic Budget Warning Modal (Dynamically Populated) -->
<div id="budgetWarningModal" class="budget-warning-modal hidden">
    <div class="modal-overlay" id="budgetWarningOverlay"></div>
    <div class="modal-container">
        <div class="modal-content">
            <!-- Icon Container (will be styled dynamically) -->
            <div class="modal-icon-container" id="budgetWarningIcon">
                <i class="fi fi-rr-trending-up" id="budgetWarningIconSymbol"></i>
            </div>

            <!-- Title -->
            <h3 class="modal-title" id="budgetWarningTitle">Bid Check</h3>

            <!-- Message -->
            <p class="modal-message" id="budgetWarningMessage"></p>

            <!-- Hint -->
            <p class="modal-hint">Would you like to continue with this amount or go back to edit it?</p>

            <!-- Action Buttons -->
            <div class="modal-buttons-row">
                <button type="button" class="modal-button modal-button-secondary" id="budgetWarningEditBtn">
                    Edit
                </button>
                <button type="button" class="modal-button modal-button-primary" id="budgetWarningContinueBtn">
                    Continue
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.budget-warning-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: none;
}

.budget-warning-modal:not(.hidden) {
    display: flex;
    align-items: center;
    justify-content: center;
}

.budget-warning-modal .modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.budget-warning-modal .modal-container {
    position: relative;
    z-index: 10001;
    max-width: 480px;
    width: 90%;
    margin: 0 auto;
}

.budget-warning-modal .modal-content {
    background-color: #ffffff;
    border-radius: 16px;
    padding: 32px 24px;
    text-align: center;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.budget-warning-modal .modal-icon-container {
    width: 64px;
    height: 64px;
    margin: 0 auto 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.budget-warning-modal .modal-icon-container.warning {
    background-color: #FEF3C7;
}

.budget-warning-modal .modal-icon-container.info {
    background-color: #DBEAFE;
}

.budget-warning-modal .modal-icon-container i {
    font-size: 32px;
}

.budget-warning-modal .modal-icon-container.warning i {
    color: #F59E0B;
}

.budget-warning-modal .modal-icon-container.info i {
    color: #3B82F6;
}

.budget-warning-modal .modal-title {
    font-size: 20px;
    font-weight: 600;
    color: #0F172A;
    margin-bottom: 12px;
}

.budget-warning-modal .modal-message {
    font-size: 15px;
    color: #64748B;
    line-height: 1.6;
    margin-bottom: 12px;
}

.budget-warning-modal .modal-hint {
    font-size: 14px;
    color: #94A3B8;
    margin-bottom: 24px;
}

.budget-warning-modal .modal-buttons-row {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.budget-warning-modal .modal-button {
    flex: 1;
    max-width: 140px;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    outline: none;
}

.budget-warning-modal .modal-button-secondary {
    background-color: #F1F5F9;
    color: #0F172A;
}

.budget-warning-modal .modal-button-secondary:hover {
    background-color: #E2E8F0;
}

.budget-warning-modal .modal-button-primary {
    background-color: #EC7E00;
    color: #ffffff;
}

.budget-warning-modal .modal-button-primary:hover {
    background-color: #C96A00;
}
</style>
