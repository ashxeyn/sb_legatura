<div id="deleteProgressModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>Delete Progress Report</h2>
            <span class="close" onclick="ProgressDelete.close()">&times;</span>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 12px; font-size: 16px;">
                Please provide a reason for deleting this progress report. This reason will be stored with the record.
            </p>
            <div style="margin-bottom: 10px;">
                <label for="delete_progress_reason" style="display:block; font-weight:600; margin-bottom:6px;">Delete Reason *</label>
                <textarea id="delete_progress_reason" name="delete_reason" rows="4" maxlength="1000" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" placeholder="Explain why this progress report should be deleted (required)"></textarea>
            </div>
            <div id="deleteProgressErrorMessage" class="error-message" style="display: none;"></div>
            <div id="deleteProgressSuccessMessage" class="success-message" style="display: none;"></div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="ProgressDelete.close()">No, Keep It</button>
            <button type="button" class="btn btn-danger" onclick="ProgressDelete.confirm()">Yes, Delete Report</button>
        </div>
    </div>
</div>

