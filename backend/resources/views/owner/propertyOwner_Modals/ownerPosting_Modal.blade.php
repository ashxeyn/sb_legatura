<!-- Post Project Modal -->
<div id="postProjectModal" class="post-project-modal">
    <div class="modal-overlay" id="modalOverlay"></div>
    <div class="modal-container">
        <!-- Modal Header -->
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fi fi-rr-edit"></i>
                <span>Create Project Post</span>
            </h2>
            <button class="modal-close-btn" id="closeModalBtn" aria-label="Close modal">
                <i class="fi fi-rr-cross"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <form id="postProjectForm" method="POST" action="/owner/projects" enctype="multipart/form-data">
                @csrf

                <!-- Success/Error Messages -->
                <div id="modalFormSuccess" class="alert alert-success hidden"></div>
                <div id="modalFormError" class="alert alert-error hidden"></div>

                <!-- Project Title -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fi fi-rr-file-edit"></i>
                        <span>Project Title <span class="required">*</span></span>
                    </label>
                    <input type="text" name="project_title" id="modal_project_title" class="form-input" placeholder="Enter project title" required maxlength="200">
                    <div class="error-message hidden" id="error_project_title"></div>
                </div>

                <!-- Project Description -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fi fi-rr-document"></i>
                        <span>Project Description <span class="required">*</span></span>
                    </label>
                    <textarea name="project_description" id="modal_project_description" class="form-textarea" placeholder="Describe your project in detail..." required></textarea>
                    <div class="error-message hidden" id="error_project_description"></div>
                </div>

                <!-- Location Fields -->
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label class="form-label">
                            <i class="fi fi-rr-marker"></i>
                            <span>Barangay <span class="required">*</span></span>
                        </label>
                        <select name="barangay" id="modal_project_barangay" class="form-select" required disabled>
                            <option value="">Loading barangays...</option>
                        </select>
                        <div class="error-message hidden" id="error_barangay"></div>
                    </div>

                    <div class="form-group form-group-half">
                        <label class="form-label">
                            <i class="fi fi-rr-home"></i>
                            <span>Street Address <span class="required">*</span></span>
                        </label>
                        <input type="text" name="street_address" id="modal_street_address" class="form-input" placeholder="Street, Purok, House No." required maxlength="255">
                        <div class="error-message hidden" id="error_street_address"></div>
                    </div>
                </div>

                <small class="form-hint">
                    <i class="fi fi-rr-info"></i>
                    City and Province are fixed to <strong>Zamboanga City</strong>, <strong>Zamboanga del Sur</strong>.
                </small>

                <!-- Hidden location fields -->
                <input type="hidden" name="project_location" id="modal_project_location_hidden">
                <input type="hidden" name="project_city_code" id="modal_project_city_code_hidden">
                <input type="hidden" name="project_province_code" id="modal_project_province_code_hidden">

                <!-- Budget Range -->
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label class="form-label">
                            <i class="fi fi-rr-money"></i>
                            <span>Budget Min (₱) <span class="required">*</span></span>
                        </label>
                        <input type="number" name="budget_range_min" id="modal_budget_range_min" class="form-input" placeholder="0.00" step="0.01" required min="0">
                        <div class="error-message hidden" id="error_budget_range_min"></div>
                    </div>

                    <div class="form-group form-group-half">
                        <label class="form-label">
                            <i class="fi fi-rr-money"></i>
                            <span>Budget Max (₱) <span class="required">*</span></span>
                        </label>
                        <input type="number" name="budget_range_max" id="modal_budget_range_max" class="form-input" placeholder="0.00" step="0.01" required min="0">
                        <div class="error-message hidden" id="error_budget_range_max"></div>
                    </div>
                </div>

                <!-- Property Details -->
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label class="form-label">
                            <i class="fi fi-rr-ruler"></i>
                            <span>Lot Size (sqm) <span class="required">*</span></span>
                        </label>
                        <input type="number" name="lot_size" id="modal_lot_size" class="form-input" placeholder="0" required min="1">
                        <div class="error-message hidden" id="error_lot_size"></div>
                    </div>

                    <div class="form-group form-group-half">
                        <label class="form-label">
                            <i class="fi fi-rr-ruler"></i>
                            <span>Floor Area (sqm) <span class="required">*</span></span>
                        </label>
                        <input type="number" name="floor_area" id="modal_floor_area" class="form-input" placeholder="0" required min="1">
                        <div class="error-message hidden" id="error_floor_area"></div>
                    </div>
                </div>

                <!-- Property Type and Contractor Type -->
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label class="form-label">
                            <i class="fi fi-rr-building"></i>
                            <span>Property Type <span class="required">*</span></span>
                        </label>
                        <select name="property_type" id="modal_property_type" class="form-select" required>
                            <option value="">Select Property Type</option>
                            <option value="Residential">Residential</option>
                            <option value="Commercial">Commercial</option>
                            <option value="Industrial">Industrial</option>
                            <option value="Agricultural">Agricultural</option>
                        </select>
                        <div class="error-message hidden" id="error_property_type"></div>
                    </div>

                    <div class="form-group form-group-half">
                        <label class="form-label">
                            <i class="fi fi-rr-wrench"></i>
                            <span>Contractor Type <span class="required">*</span></span>
                        </label>
                        <select name="type_id" id="modal_project_type_id" class="form-select" required>
                            <option value="">Select Contractor Type</option>
                            @if(isset($contractorTypes))
                                @php
                                    $othersOption = null;
                                @endphp
                                @foreach($contractorTypes as $type)
                                    @if(strtolower(trim($type->type_name)) === 'others')
                                        @php $othersOption = $type; continue; @endphp
                                    @endif
                                    <option value="{{ $type->type_id }}" data-name="{{ $type->type_name }}">
                                        {{ $type->type_name }}
                                    </option>
                                @endforeach
                                @if($othersOption)
                                    <option value="{{ $othersOption->type_id }}" data-name="{{ $othersOption->type_name }}">
                                        {{ $othersOption->type_name }}
                                    </option>
                                @endif
                            @endif
                        </select>
                        <div class="error-message hidden" id="error_type_id"></div>
                    </div>
                </div>

                <!-- Other Contractor Type (conditional) -->
                <div class="form-group hidden" id="modal_other_contractor_type_container">
                    <label class="form-label">
                        <i class="fi fi-rr-edit"></i>
                        <span>Specify Contractor Type <span class="required">*</span></span>
                    </label>
                    <input type="text" name="if_others_ctype" id="modal_if_others_ctype" class="form-input" placeholder="Specify contractor type" maxlength="200">
                    <div class="error-message hidden" id="error_if_others_ctype"></div>
                </div>

                <!-- Bidding Deadline -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fi fi-rr-calendar"></i>
                        <span>Bidding Deadline <span class="required">*</span></span>
                    </label>
                    <input type="date" name="bidding_deadline" id="modal_bidding_deadline" class="form-input" required>
                    <div class="error-message hidden" id="error_bidding_deadline"></div>
                </div>

                <!-- File Uploads -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fi fi-rr-file"></i>
                        <span>Building Permit <span class="required">*</span></span>
                    </label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="modal_building_permit" name="building_permit" accept=".jpg,.jpeg,.png" class="file-input" required>
                        <label for="modal_building_permit" class="file-upload-label">
                            <i class="fi fi-rr-upload"></i>
                            <span>Choose Building Permit Image</span>
                        </label>
                        <div class="file-name-display" id="building_permit_name"></div>
                    </div>
                    <small class="form-hint">Accepted: JPG, JPEG, PNG (Max 10MB)</small>
                    <div class="error-message hidden" id="error_building_permit"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fi fi-rr-file"></i>
                        <span>Land Title <span class="required">*</span></span>
                    </label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="modal_land_title" name="land_title" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="file-input" required>
                        <label for="modal_land_title" class="file-upload-label">
                            <i class="fi fi-rr-upload"></i>
                            <span>Choose Land Title Document</span>
                        </label>
                        <div class="file-name-display" id="land_title_name"></div>
                    </div>
                    <small class="form-hint">Accepted: PDF, DOC, DOCX, JPG, JPEG, PNG (Max 10MB)</small>
                    <div class="error-message hidden" id="error_land_title"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fi fi-rr-file"></i>
                        <span>Others (Optional)</span>
                    </label>
                    <div id="modal_others_upload_container">
                        <div class="file-upload-wrapper">
                            <input type="file" id="modal_others_0" name="others[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="file-input">
                            <label for="modal_others_0" class="file-upload-label">
                                <i class="fi fi-rr-upload"></i>
                                <span>Choose File</span>
                            </label>
                        </div>
                    </div>
                    <button type="button" class="add-file-btn" id="modal_add_others_file">
                        <i class="fi fi-rr-plus"></i>
                        <span>Add More Files</span>
                    </button>
                    <small class="form-hint">Accepted: PDF, DOC, DOCX, JPG, JPEG, PNG (Max 10MB each, up to 10 files)</small>
                    <div class="error-message hidden" id="error_others"></div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" id="cancelModalBtn">Cancel</button>
                    <button type="submit" class="btn btn-submit" id="submitProjectBtn">
                        <i class="fi fi-rr-check"></i>
                        <span>Post Project</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

