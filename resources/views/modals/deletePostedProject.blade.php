<div id="deleteProjectModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2>Delete Project</h2>
            <span class="close" onclick="ProjectDelete.close()">&times;</span>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 20px; font-size: 16px;">
                Are you sure you want to delete this project? This action cannot be undone.
            </p>

            <div style="margin-bottom: 20px;">
                <label for="delete_reason" style="display: block; margin-bottom: 8px; font-weight: 600;">
                    Reason for Deletion <span style="color: red;">*</span>
                </label>
                <textarea
                    id="delete_reason"
                    name="reason"
                    rows="4"
                    maxlength="500"
                    placeholder="Please provide a reason for deleting this project..."
                    style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical;"
                    required
                ></textarea>
                <small style="color: #666; display: block; margin-top: 4px;">
                    <span id="delete_reason_count">0</span> / 500 characters
                </small>
            </div>

            <div id="deleteProjectErrorMessage" class="error-message" style="display: none;"></div>
            <div id="deleteProjectSuccessMessage" class="success-message" style="display: none;"></div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="ProjectDelete.close()">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmDeleteBtn" onclick="ProjectDelete.confirm()">Yes, Delete Project</button>
        </div>
    </div>
</div>

<script>
// Character counter for delete reason
document.addEventListener('DOMContentLoaded', function() {
    const deleteReasonTextarea = document.getElementById('delete_reason');
    const deleteReasonCount = document.getElementById('delete_reason_count');

    if (deleteReasonTextarea && deleteReasonCount) {
        deleteReasonTextarea.addEventListener('input', function() {
            deleteReasonCount.textContent = this.value.length;
        });
    }
});
</script>
