/**
 * bidManagement.js
 *
 * This file intentionally contains NO modal logic, NO listener registration
 * for .btn-view-bid / .btn-edit-bid / .btn-delete-bid, and NO references to
 * saveChangesBtn / confirmSaveBtn / confirmDeleteBtn / cancelDeleteBtn.
 *
 * ALL modal logic lives in the inline <script> block inside bidManagement.blade.php
 * which uses the [data-modal] + .modal-active pattern.
 *
 * This file only provides:
 *   - formatPHP()         – currency formatter (used by blade inline script via window)
 *   - calculateDuration() – timeline helper (used by blade inline script via window)
 *   - Any future non-modal helpers
 *
 * DO NOT add open(), close(), or any getElementById() for modal elements here.
 */

// Expose helpers globally so the blade inline script can use them if needed
window.BidMgmt = {
    formatPHP: function (value) {
        if (value === null || value === undefined) return '';
        const num = typeof value === 'number'
            ? value
            : Number(String(value).replace(/[^0-9.-]/g, ''));
        if (Number.isNaN(num)) return '';
        return num.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    },

    calculateDuration: function (startDateStr, endDateStr) {
        if (!startDateStr || !endDateStr) return '—';
        const start    = new Date(startDateStr);
        const end      = new Date(endDateStr);
        const diffDays = Math.ceil(Math.abs(end - start) / 86400000);
        const months   = Math.floor(diffDays / 30);
        const days     = diffDays % 30;
        return months + ' month(s) / ' + days + ' day(s)';
    },
};