@php
    $projectId = $project->project_id;
    $minBudget = $project->budget_range_min ?? 0;
    $maxBudget = $project->budget_range_max ?? 0;
@endphp

<!-- Budget Warning Confirmation Modal for Project {{ $projectId }} -->
<div id="budgetWarningModal-{{ $projectId }}" class="budget-warning-modal hidden">
    <div class="modal-overlay" onclick="closeBudgetWarningModal('{{ $projectId }}')"></div>
    <div class="modal-container">
        <div class="modal-content">
            <!-- Icon Container (will be styled dynamically) -->
            <div class="modal-icon-container" id="budgetWarningIcon-{{ $projectId }}">
                <i class="fi fi-rr-trending-up" id="budgetWarningIconSymbol-{{ $projectId }}"></i>
            </div>

            <!-- Title -->
            <h3 class="modal-title" id="budgetWarningTitle-{{ $projectId }}">Bid Above Budget Range</h3>

            <!-- Message -->
            <p class="modal-message" id="budgetWarningMessage-{{ $projectId }}"></p>

            <!-- Hint -->
            <p class="modal-hint">Would you like to continue with this bid amount or go back to edit it?</p>

            <!-- Action Buttons -->
            <div class="modal-buttons-row">
                <button type="button" class="modal-button modal-button-secondary"
                    onclick="handleEditBudget('{{ $projectId }}')">
                    Edit
                </button>
                <button type="button" class="modal-button modal-button-primary"
                    onclick="handleContinueBudget('{{ $projectId }}')">
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
        z-index: 9002;
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
        z-index: 9001;
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