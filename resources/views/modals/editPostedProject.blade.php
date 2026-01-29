<div id="editProjectModal" class="modal">
    <div class="modal-content" style="max-width: 600px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h2 id="editProjectModalTitle">Edit Project Post</h2>
            <span class="close" onclick="ProjectEdit.close()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="editProjectErrorMessages" class="error-message" style="display:none;"></div>
            <div id="editProjectSuccessMessages" class="success-message" style="display:none;"></div>

            <form id="editProjectModalForm">
                <input type="hidden" id="edit_project_id" name="project_id">

                <div class="form-group">
                    <label for="edit_project_title">Project Title <span class="required">*</span></label>
                    <input type="text" id="edit_project_title" name="project_title" required maxlength="200">
                </div>

                <div class="form-group">
                    <label for="edit_project_description">Project Description <span class="required">*</span></label>
                    <textarea id="edit_project_description" name="project_description" required rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label for="edit_barangay">Barangay <span class="required">*</span></label>
                    <select id="edit_barangay" name="barangay" required disabled>
                        <option value="">Loading barangays...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_street_address">Street / Barangay Details <span class="required">*</span></label>
                    <input type="text" id="edit_street_address" name="street_address" required maxlength="255" placeholder="Street, Purok, House No. etc">
                    <small style="display:block; margin-top:6px; color:#666;">City and Province are fixed to <strong>Zamboanga City</strong>, <strong>Zamboanga del Sur</strong>.</small>
                </div>

                <input type="hidden" id="edit_project_location_hidden" name="project_location">
                <input type="hidden" id="edit_project_city_code_hidden" name="project_city_code">
                <input type="hidden" id="edit_project_province_code_hidden" name="project_province_code">

                <div class="form-group">
                    <label for="edit_budget_range_min">Budget Range (Min) <span class="required">*</span></label>
                    <input type="number" id="edit_budget_range_min" name="budget_range_min" step="0.01" required min="0">
                </div>

                <div class="form-group">
                    <label for="edit_budget_range_max">Budget Range (Max) <span class="required">*</span></label>
                    <input type="number" id="edit_budget_range_max" name="budget_range_max" step="0.01" required min="0">
                </div>

                <div class="form-group">
                    <label for="edit_lot_size">Lot Size (sqm) <span class="required">*</span></label>
                    <input type="number" id="edit_lot_size" name="lot_size" required min="1">
                </div>

                <div class="form-group">
                    <label for="edit_floor_area">Floor Area (sqm) <span class="required">*</span></label>
                    <input type="number" id="edit_floor_area" name="floor_area" required min="1">
                </div>

                <div class="form-group">
                    <label for="edit_property_type">Property Type <span class="required">*</span></label>
                    <select id="edit_property_type" name="property_type" required>
                        <option value="">Select Property Type</option>
                        <option value="residential">Residential</option>
                        <option value="commercial">Commercial</option>
                        <option value="industrial">Industrial</option>
                        <option value="agricultural">Agricultural</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_type_id">Contractor Type Required <span class="required">*</span></label>
                    <select id="edit_type_id" name="type_id" required>
                        <option value="">Select Contractor Type</option>
                        @if(isset($contractorTypes))
                            @php
                                $othersOption = null;
                            @endphp
                            @foreach($contractorTypes as $type)
                                @if(strtolower(trim($type->type_name)) === 'others')
                                    @php $othersOption = $type; continue; @endphp
                                @endif
                                <option value="{{ $type->type_id }}" data-name="{{ $type->type_name }}">{{ $type->type_name }}</option>
                            @endforeach
                            @if($othersOption)
                                <option value="{{ $othersOption->type_id }}" data-name="{{ $othersOption->type_name }}">{{ $othersOption->type_name }}</option>
                            @endif
                        @endif
                    </select>

                    <div id="edit_if_others_group" style="display: none; margin-top:10px;">
                        <label for="edit_if_others_ctype">If Others, specify contractor type <span class="required">*</span></label>
                        <input type="text" id="edit_if_others_ctype" name="if_others_ctype" maxlength="200" placeholder="Specify contractor type">
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_bidding_deadline">Bidding Deadline <span class="required">*</span></label>
                    <input type="date" id="edit_bidding_deadline" name="bidding_deadline" required>
                </div>

                <div class="form-group">
                    <label>Building Permit</label>
                    <div id="edit-building-permit-container">
                        <div id="existing-building-permit-section" style="display:none; margin-bottom:12px;">
                            <div id="existing-building-permit-link" style="margin-bottom:8px; padding:10px; background:#f8f9fa; border-radius:6px;"></div>
                        </div>
                        <button type="button" id="choose-building-permit-btn" class="btn btn-secondary" style="display:none;">Choose Another File</button>
                        <div class="file-input-group" id="building-permit-input-group">
                            <input type="file" id="edit_building_permit" name="building_permit" accept=".jpg,.jpeg,.png" class="evidence-file-input" style="display:block;">
                        </div>
                    </div>
                    <small>Leave empty to keep existing file. Accepted formats: JPG, JPEG, PNG (Max 10MB).</small>
                    <div id="current-building-permit" style="margin-top: 5px; font-size: 12px; color: #65676b;"></div>
                </div>

                <div class="form-group">
                    <label>Title of the Land</label>
                    <div id="edit-title-of-land-container">
                        <div id="existing-title-of-land-section" style="display:none; margin-bottom:12px;">
                            <div id="existing-title-of-land-link" style="margin-bottom:8px; padding:10px; background:#f8f9fa; border-radius:6px;"></div>
                        </div>
                        <button type="button" id="choose-title-of-land-btn" class="btn btn-secondary" style="display:none;">Choose Another File</button>
                        <div class="file-input-group" id="title-of-land-input-group">
                            <input type="file" id="edit_title_of_land" name="title_of_land" accept=".jpg,.jpeg,.png" class="evidence-file-input" style="display:block;">
                        </div>
                    </div>
                    <small>Leave empty to keep existing file. Accepted formats: JPG, JPEG, PNG (Max 10MB).</small>
                    <div id="current-title-of-land" style="margin-top: 5px; font-size: 12px; color: #65676b;"></div>
                </div>

                <div class="form-group">
                    <label>Blueprint (Optional)</label>
                    <div id="edit-blueprint-upload-container">
                        <div class="file-input-group">
                            <input type="file" id="edit_blueprint" name="blueprint[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="evidence-file-input" onchange="handleEditFileSelection(this, 'edit-blueprint-add-more')">
                            <button type="button" class="remove-file-btn" style="display:none;" onclick="removeEditFileInput(this, 'edit-blueprint-upload-container', 'edit-blueprint-add-more')">Remove</button>
                        </div>
                    </div>
                    <button type="button" id="edit-blueprint-add-more" class="add-more-files-btn" style="display:none;" onclick="addMoreEditFiles('edit-blueprint-upload-container', 'edit_blueprint')">Add More Files</button>
                    <small>Leave empty to keep existing files. Accepted formats: JPG, JPEG, PNG, PDF, DOC, DOCX (Max 10MB each, up to 10 files)</small>
                    <div id="current-blueprint" style="margin-top: 5px; font-size: 12px; color: #65676b;"></div>
                </div>

                <div class="form-group">
                    <label>Desired Design (Optional)</label>
                    <div id="edit-desired-design-upload-container">
                        <div class="file-input-group">
                            <input type="file" id="edit_desired_design" name="desired_design[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="evidence-file-input" onchange="handleEditFileSelection(this, 'edit-desired-design-add-more')">
                            <button type="button" class="remove-file-btn" style="display:none;" onclick="removeEditFileInput(this, 'edit-desired-design-upload-container', 'edit-desired-design-add-more')">Remove</button>
                        </div>
                    </div>
                    <button type="button" id="edit-desired-design-add-more" class="add-more-files-btn" style="display:none;" onclick="addMoreEditFiles('edit-desired-design-upload-container', 'edit_desired_design')">Add More Files</button>
                    <small>Leave empty to keep existing files. Accepted formats: JPG, JPEG, PNG, PDF, DOC, DOCX (Max 10MB each, up to 10 files)</small>
                    <div id="current-desired-design" style="margin-top: 5px; font-size: 12px; color: #65676b;"></div>
                </div>

                <div class="form-group">
                    <label>Others (Optional - Multiple Files)</label>
                    <div id="edit-others-upload-container">
                        <div class="file-input-group">
                            <input type="file" id="edit_others" name="others[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="evidence-file-input" onchange="handleEditFileSelection(this, 'edit-others-add-more')">
                            <button type="button" class="remove-file-btn" style="display:none;" onclick="removeEditFileInput(this, 'edit-others-upload-container', 'edit-others-add-more')">Remove</button>
                        </div>
                    </div>
                    <button type="button" id="edit-others-add-more" class="add-more-files-btn" style="display:none;" onclick="addMoreEditFiles('edit-others-upload-container', 'edit_others')">Add More Files</button>
                    <small>Leave empty to keep existing files. Accepted formats: JPG, JPEG, PNG, PDF, DOC, DOCX (Max 10MB each, up to 10 files)</small>
                    <div id="current-others" style="margin-top: 5px; font-size: 12px; color: #65676b;"></div>
                </div>
            </form>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="ProjectEdit.close()">Cancel</button>
            <button type="button" id="editProjectSubmitBtn" class="btn btn-primary" onclick="ProjectEdit.submit()">Update Project</button>
        </div>
    </div>
</div>

<style>
    #editProjectModal .form-group {
        margin-bottom: 20px;
    }

    #editProjectModal label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #1c1e21;
        font-size: 15px;
    }

    #editProjectModal .required {
        color: #e41e3f;
    }

    #editProjectModal input[type="text"],
    #editProjectModal input[type="number"],
    #editProjectModal input[type="date"],
    #editProjectModal select,
    #editProjectModal textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccd0d5;
        border-radius: 6px;
        font-family: inherit;
        font-size: 15px;
    }

    #editProjectModal textarea {
        resize: vertical;
        min-height: 100px;
    }

    #editProjectModal input:focus,
    #editProjectModal select:focus,
    #editProjectModal textarea:focus {
        outline: none;
        border-color: #1877f2;
    }

    #editProjectModal small {
        color: #65676b;
        font-size: 12px;
        display: block;
        margin-top: 5px;
    }

    #editProjectModal .file-input-group {
        margin-bottom: 10px;
        position: relative;
    }

    #editProjectModal .evidence-file-input {
        display: inline-block;
        width: calc(100% - 90px);
        padding: 8px;
        border: 1px solid #ccd0d5;
        border-radius: 6px;
        font-size: 14px;
    }

    #editProjectModal .remove-file-btn {
        display: inline-block;
        margin-left: 5px;
        padding: 8px 12px;
        background-color: #e4e6eb;
        color: #050505;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
    }

    #editProjectModal .remove-file-btn:hover {
        background-color: #d8dadf;
    }

    #editProjectModal .add-more-files-btn {
        padding: 8px 16px;
        background-color: #e4e6eb;
        color: #050505;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        margin-top: 5px;
    }

    #editProjectModal .add-more-files-btn:hover {
        background-color: #d8dadf;
    }
</style>

<script>
// Add contractor type change handler for edit modal
document.addEventListener('DOMContentLoaded', function() {
    const editTypeSelect = document.getElementById('edit_type_id');
    const editIfOthersGroup = document.getElementById('edit_if_others_group');
    const editIfOthersInput = document.getElementById('edit_if_others_ctype');

    if (editTypeSelect) {
        editTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption) {
                const typeName = selectedOption.getAttribute('data-name') || selectedOption.text;
                if (typeName && typeName.toLowerCase() === 'others') {
                    if (editIfOthersGroup) editIfOthersGroup.style.display = 'block';
                    if (editIfOthersInput) editIfOthersInput.setAttribute('required', 'required');
                } else {
                    if (editIfOthersGroup) editIfOthersGroup.style.display = 'none';
                    if (editIfOthersInput) {
                        editIfOthersInput.removeAttribute('required');
                        editIfOthersInput.value = '';
                    }
                }
            }
        });
    }
});

// File management functions for edit modal
function handleEditFileSelection(input, addMoreBtnId) {
    const fileGroup = input.parentElement;
    const removeBtn = fileGroup.querySelector('.remove-file-btn');
    const addMoreBtn = document.getElementById(addMoreBtnId);

    if (input.files && input.files.length > 0) {
        // Show file name in green background
        const fileName = input.files[0].name;
        let fileDisplay = fileGroup.querySelector('.file-name-display');

        if (!fileDisplay) {
            fileDisplay = document.createElement('div');
            fileDisplay.className = 'file-name-display';
            fileDisplay.style.padding = '8px 12px';
            fileDisplay.style.background = '#d4edda';
            fileDisplay.style.color = '#155724';
            fileDisplay.style.borderRadius = '4px';
            fileDisplay.style.marginBottom = '8px';
            fileDisplay.style.fontSize = '14px';
            fileGroup.insertBefore(fileDisplay, input);
        }

        fileDisplay.textContent = fileName;
        fileDisplay.style.display = 'block';
        input.style.display = 'none';

        // Show remove button
        if (removeBtn) {
            removeBtn.style.display = 'inline-block';
        }

        // Show Add More button
        if (addMoreBtn) {
            addMoreBtn.style.display = 'inline-block';
        }
    }
}

function addMoreEditFiles(containerId, inputBaseName) {
    const container = document.getElementById(containerId);
    const fileInputs = container.querySelectorAll('input[type="file"]');

    if (fileInputs.length >= 10) {
        alert('Maximum 10 files allowed');
        return;
    }

    const addMoreBtnId = containerId.replace('-upload-container', '-add-more');

    const newGroup = document.createElement('div');
    newGroup.className = 'file-input-group';
    newGroup.style.marginTop = '10px';

    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.name = inputBaseName + '[]';
    fileInput.accept = '.pdf,.doc,.docx,.jpg,.jpeg,.png';
    fileInput.className = 'evidence-file-input';
    fileInput.style.display = 'none';

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'remove-file-btn';
    removeBtn.textContent = 'Remove';
    removeBtn.style.display = 'none';
    removeBtn.onclick = function() { removeEditFileInput(this, containerId, addMoreBtnId); };

    newGroup.appendChild(fileInput);
    newGroup.appendChild(removeBtn);
    container.appendChild(newGroup);

    // Automatically trigger file picker
    fileInput.click();

    // Show file name when selected
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const fileName = this.files[0].name;

            // Create file display
            const fileDisplay = document.createElement('div');
            fileDisplay.className = 'file-name-display';
            fileDisplay.style.padding = '8px 12px';
            fileDisplay.style.background = '#d4edda';
            fileDisplay.style.color = '#155724';
            fileDisplay.style.borderRadius = '4px';
            fileDisplay.style.marginBottom = '8px';
            fileDisplay.style.fontSize = '14px';
            fileDisplay.textContent = fileName;

            newGroup.insertBefore(fileDisplay, fileInput);
            removeBtn.style.display = 'inline-block';
        } else {
            // If no file selected, remove the empty input group
            newGroup.remove();
        }
    });
}

function removeExistingFile(fileId, fileType) {
    // Remove the file row from the display
    const fileRow = document.getElementById('existing-file-' + fileId);
    if (fileRow) {
        fileRow.remove();
    }

    // Check if there are any remaining files of this type
    const divMapping = {
        'blueprint': { divId: 'current-blueprint', containerId: 'edit-blueprint-upload-container', btnId: 'edit-blueprint-add-more' },
        'desired design': { divId: 'current-desired-design', containerId: 'edit-desired-design-upload-container', btnId: 'edit-desired-design-add-more' },
        'others': { divId: 'current-others', containerId: 'edit-others-upload-container', btnId: 'edit-others-add-more' }
    };

    const mapping = divMapping[fileType];
    if (mapping) {
        const div = document.getElementById(mapping.divId);
        const table = div ? div.querySelector('table') : null;
        const remainingRows = table ? table.querySelectorAll('tbody tr').length : 0;

        if (remainingRows === 0) {
            // No more existing files, clear the div and show the file input
            if (div) div.innerHTML = '';

            const container = document.getElementById(mapping.containerId);
            if (container && container.children.length === 0) {
                // Re-add the initial file input
                const inputName = mapping.containerId.includes('blueprint') ? 'blueprint[]' :
                                 mapping.containerId.includes('desired') ? 'desired_design[]' : 'others[]';
                const inputId = mapping.containerId.replace('-upload-container', '').replace('edit-', 'edit_');

                container.innerHTML = `
                    <div class="file-input-group">
                        <input type="file" id="${inputId}" name="${inputName}" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="evidence-file-input" onchange="handleEditFileSelection(this, '${mapping.btnId}')">
                        <button type="button" class="remove-file-btn" style="display:none;" onclick="removeEditFileInput(this, '${mapping.containerId}', '${mapping.btnId}')">Remove</button>
                    </div>
                `;
            }

            // Hide Add More button
            const addMoreBtn = document.getElementById(mapping.btnId);
            if (addMoreBtn) addMoreBtn.style.display = 'none';
        }
    }

    // Track deleted file IDs (you may want to send these to the server on form submit)
    if (!window.deletedFileIds) {
        window.deletedFileIds = [];
    }
    window.deletedFileIds.push(fileId);
}

function removeEditFileInput(button, containerId, addMoreBtnId) {
    const container = document.getElementById(containerId);
    const fileGroup = button.closest('.file-input-group');
    const fileInputs = container.querySelectorAll('.file-input-group');

    if (fileInputs.length > 1) {
        fileGroup.remove();

        // Hide Add More button if only one file input remains after removal
        const remainingInputs = container.querySelectorAll('.file-input-group');
        const addMoreBtn = document.getElementById(addMoreBtnId);
        if (remainingInputs.length === 1 && addMoreBtn) {
            const firstInput = remainingInputs[0].querySelector('input[type="file"]');
            if (firstInput && firstInput.files.length === 0) {
                addMoreBtn.style.display = 'none';
            }
        }
    } else {
        // Last file input - just clear it and hide remove button
        const input = fileGroup.querySelector('input[type="file"]');
        const fileDisplay = fileGroup.querySelector('.file-name-display');

        if (input) {
            input.value = '';
            input.style.display = 'block';
        }
        if (fileDisplay) {
            fileDisplay.remove();
        }

        button.style.display = 'none';

        // Hide Add More button
        if (addMoreBtnId) {
            const addMoreBtn = document.getElementById(addMoreBtnId);
            if (addMoreBtn) addMoreBtn.style.display = 'none';
        }
    }
}

// Show/hide remove buttons based on file selection
document.addEventListener('DOMContentLoaded', function() {
    const editFileInputs = document.querySelectorAll('#editProjectModal input[type="file"]');
    editFileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const removeBtn = this.parentElement.querySelector('.remove-file-btn');
            if (removeBtn) {
                removeBtn.style.display = this.files.length > 0 ? 'inline-block' : 'none';
            }
        });
    });
});
</script>
