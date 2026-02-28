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
            @php
                // Get user's city from profile
                $sessionUser = session('user');
                $userCity = null;
                $userCityName = 'Zamboanga City';
                $userProvinceName = 'Zamboanga del Sur';
                $cityCode = '097332000'; // Default to Zamboanga City
                // Hostinger/Linux: Table/field names are case-sensitive. Use lowercase/underscored names as in migrations.
                if ($sessionUser && isset($sessionUser->user_id)) {
                    try {
                        $ownerProfile = \DB::table('property_owners')
                            ->where('user_id', $sessionUser->user_id)
                            ->first();
                        if ($ownerProfile && isset($ownerProfile->city)) {
                            $userCity = $ownerProfile->city;
                            $userCityName = $userCity;
                            // Fetch city details from PSGC to get the code
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, 'https://psgc.gitlab.io/api/cities-municipalities');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            $response = curl_exec($ch);
                            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            curl_close($ch);
                            if ($http_code === 200 && !empty($response)) {
                                $cities = json_decode($response, true);
                                if (is_array($cities)) {
                                    foreach ($cities as $city) {
                                        if (isset($city['name']) && stripos($city['name'], $userCity) !== false) {
                                            $cityCode = $city['code'];
                                            $userCityName = $city['name'];
                                            if (isset($city['provinceCode'])) {
                                                // Use province code to get province name from PSGC API (case-sensitive field names)
                                                $provinceCode = $city['provinceCode'];
                                                $ch2 = curl_init();
                                                curl_setopt($ch2, CURLOPT_URL, 'https://psgc.gitlab.io/api/provinces/' . $provinceCode);
                                                curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                                                curl_setopt($ch2, CURLOPT_TIMEOUT, 3);
                                                curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
                                                $repResponse = curl_exec($ch2);
                                                curl_close($ch2);
                                                if (!empty($repResponse)) {
                                                    $provinceData = json_decode($repResponse, true);
                                                    if (is_array($provinceData) && isset($provinceData['name'])) {
                                                        $userProvinceName = $provinceData['name'];
                                                    } else {
                                                        $userProvinceName = 'Region ' . substr($provinceCode, 0, 2);
                                                    }
                                                } else {
                                                    $userProvinceName = 'Region ' . substr($provinceCode, 0, 2);
                                                }
                                            }
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Failed to fetch user city: ' . $e->getMessage());
                    }
                }
                
                // Fetch barangays from PSGC API
                $barangays = [];
                $barangaysLoaded = false;
                $barangaysError = null;
                
                try {
                    // Fetch from PSGC API
                    if (function_exists('curl_init')) {
                        $url = 'https://psgc.gitlab.io/api/cities-municipalities/' . $cityCode . '/barangays/';
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        
                        $response = curl_exec($ch);
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $curl_error = curl_error($ch);
                        curl_close($ch);
                        
                        if ($http_code === 200 && !empty($response)) {
                            $arrayResponse = json_decode($response, true);
                            if (is_array($arrayResponse) && count($arrayResponse) > 0) {
                                $barangays = array_map(function($b) {
                                    return ['code' => $b['code'] ?? '', 'name' => $b['name'] ?? ''];
                                }, $arrayResponse);
                                $barangaysLoaded = true;
                            } else {
                                $barangaysError = 'No barangays found for this city.';
                            }
                        } else {
                            $barangaysError = 'Unable to fetch barangays. Please try again later.';
                            \Log::warning('PSGC API error - HTTP ' . $http_code);
                        }
                    } else {
                        $barangaysError = 'cURL is not available on this server.';
                    }
                } catch (\Exception $e) {
                    $barangaysError = 'Error loading barangays: ' . $e->getMessage();
                    \Log::warning('Failed to fetch barangays from PSGC API: ' . $e->getMessage());
                }
                
                // Sort alphabetically by name
                usort($barangays, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });


                // Fetch contractor types from database
                $contractorTypes = [];
                $othersType = null;
                try {
                    // Table/field names are case-sensitive on Linux/Hostinger. Use lowercase/underscored names.
                    $allTypes = \DB::table('contractor_types')->get();
                    foreach ($allTypes as $type) {
                        if (isset($type->type_name) && strtolower(trim($type->type_name)) === 'others') {
                            $othersType = $type;
                        } else {
                            $contractorTypes[] = $type;
                        }
                    }
                    if ($othersType) {
                        $contractorTypes[] = $othersType;
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to fetch contractor types: ' . $e->getMessage());
                }

                // Get property types (hardcoded, matching mobile app)
                $propertyTypes = [
                    'Residential',
                    'Commercial',
                    'Industrial',
                    'Agricultural'
                ];

                // Get today's date for minimum bidding deadline
                $minBiddingDate = \Carbon\Carbon::tomorrow()->format('Y-m-d');
                $maxBiddingDate = \Carbon\Carbon::now()->addYear()->format('Y-m-d');
            @endphp

            <form id="postProjectForm" method="POST" action="/owner/projects" enctype="multipart/form-data" onsubmit="cleanFormValues()">
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

                <!-- Location Section Header -->
                <div class="form-section-header">
                    <i class="fi fi-rr-marker"></i>
                    <h3 class="section-title">Project Location</h3>
                </div>

                <!-- Location Fields -->
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label class="form-label">
                            <i class="fi fi-rr-marker"></i>
                            <span>Barangay <span class="required">*</span></span>
                        </label>
                        <select name="barangay" id="modal_project_barangay" class="form-select" required>
                            @if($barangaysError)
                                <option value="" disabled selected>❌ {{ $barangaysError }}</option>
                            @else
                                <option value="">
                                    @if($barangaysLoaded && count($barangays) > 0)
                                        Select Barangay
                                    @else
                                        No barangays available
                                    @endif
                                </option>
                                @if($barangaysLoaded && count($barangays) > 0)
                                    @foreach($barangays as $barangay)
                                        <option value="{{ $barangay['code'] }}" data-name="{{ $barangay['name'] }}">
                                            {{ $barangay['name'] }}
                                        </option>
                                    @endforeach
                                @endif
                            @endif
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
                    City and Province are fixed to <strong>{{ $userCityName }}</strong>, <strong>{{ $userProvinceName }}</strong>.
                </small>

                <!-- Hidden location fields -->
                <input type="hidden" name="project_location" id="modal_project_location_hidden">
                <input type="hidden" name="project_city_code" id="modal_project_city_code_hidden" value="{{ $cityCode }}">
                <input type="hidden" name="project_province_code" id="modal_project_province_code_hidden">
                <input type="hidden" id="modal_user_city_name" value="{{ $userCityName }}">

                <!-- Budget Range Section Header -->
                <div class="form-section-header">
                    <i class="fi fi-rr-money"></i>
                    <h3 class="section-title">Budget Range</h3>
                </div>

                <!-- Budget Range -->
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label class="form-label">
                            <i class="fi fi-rr-money"></i>
                            <span>Budget Min (₱) <span class="required">*</span></span>
                        </label>
                        <input type="text" name="budget_range_min" id="modal_budget_range_min" class="form-input" placeholder="0" oninput="formatBudgetInput(this)" required>
                        <div class="error-message hidden" id="error_budget_range_min"></div>
                    </div>

                    <div class="form-group form-group-half">
                        <label class="form-label">
                            <i class="fi fi-rr-money"></i>
                            <span>Budget Max (₱) <span class="required">*</span></span>
                        </label>
                        <input type="text" name="budget_range_max" id="modal_budget_range_max" class="form-input" placeholder="0" oninput="formatBudgetInput(this)" required>
                        <div class="error-message hidden" id="error_budget_range_max"></div>
                    </div>
                </div>

                <!-- Property Details Section Header -->
                <div class="form-section-header">
                    <i class="fi fi-rr-home"></i>
                    <h3 class="section-title">Property Details</h3>
                </div>

                <!-- Property Details -->
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label class="form-label">
                            <i class="fi fi-rr-ruler"></i>
                            <span>Lot Size (sqm) <span class="required">*</span></span>
                        </label>
                        <input type="text" name="lot_size" id="modal_lot_size" class="form-input" placeholder="0" oninput="formatNumberInput(this)" required>
                        <div class="error-message hidden" id="error_lot_size"></div>
                    </div>

                    <div class="form-group form-group-half">
                        <label class="form-label">
                            <i class="fi fi-rr-ruler"></i>
                            <span>Floor Area (sqm) <span class="required">*</span></span>
                        </label>
                        <input type="text" name="floor_area" id="modal_floor_area" class="form-input" placeholder="0" oninput="formatNumberInput(this)" required>
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
                            @foreach($propertyTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
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
                            @forelse($contractorTypes as $type)
                                <option value="{{ $type->type_id }}" data-name="{{ $type->type_name }}">
                                    {{ $type->type_name }}
                                </option>
                            @empty
                                <option value="">No contractor types available</option>
                            @endforelse
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
                    <input type="date" name="bidding_deadline" id="modal_bidding_deadline" class="form-input" required min="{{ $minBiddingDate }}" max="{{ $maxBiddingDate }}">
                    <div class="error-message hidden" id="error_bidding_deadline"></div>
                </div>

                <!-- Required Documents Section Header -->
                <div class="form-section-header">
                    <i class="fi fi-rr-file"></i>
                    <h3 class="section-title">Required Documents</h3>
                </div>

                <!-- File Uploads -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fi fi-rr-file"></i>
                        <span>Building Permit <span class="required">*</span></span>
                    </label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="modal_building_permit" name="building_permit" accept=".jpg,.jpeg,.png" class="file-input" required onchange="previewRequiredFile(this, 'building_permit')">
                        <label for="modal_building_permit" class="file-upload-label" id="building_permit_upload_label">
                            <i class="fi fi-rr-upload"></i>
                            <span>Choose Building Permit Image</span>
                            <div class="file-preview-container hidden" id="building_permit_preview_container">
                                <div class="file-preview-display" id="building_permit_preview"></div>
                                <button type="button" class="remove-file-icon-btn" onclick="removeRequiredFile(this, 'building_permit')" title="Delete file">
                                    <i class="fi fi-rr-trash"></i>
                                </button>
                            </div>
                        </label>
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
                        <input type="file" id="modal_title_of_land" name="title_of_land" accept=".jpg,.jpeg,.png" class="file-input" required onchange="previewRequiredFile(this, 'title_of_land')">
                        <label for="modal_title_of_land" class="file-upload-label" id="title_of_land_upload_label">
                            <i class="fi fi-rr-upload"></i>
                            <span>Choose Land Title Image</span>
                            <div class="file-preview-container hidden" id="title_of_land_preview_container">
                                <div class="file-preview-display" id="title_of_land_preview"></div>
                                <button type="button" class="remove-file-icon-btn" onclick="removeRequiredFile(this, 'title_of_land')" title="Delete file">
                                    <i class="fi fi-rr-trash"></i>
                                </button>
                            </div>
                        </label>
                    </div>
                    <small class="form-hint">Accepted: JPG, JPEG, PNG (Max 10MB)</small>
                    <div class="error-message hidden" id="error_title_of_land"></div>
                </div>

                <!-- Optional Documents Section Header -->
                <div class="form-section-header">
                    <i class="fi fi-rr-folder-open"></i>
                    <h3 class="section-title">Optional Documents</h3>
                </div>

                <!-- Optional Documents Section -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fi fi-rr-file"></i>
                        <span>Blueprint Images (Optional)</span>
                    </label>
                    <div id="modal_blueprint_upload_container">
                        <div class="file-upload-wrapper">
                            <input type="file" id="modal_blueprint_0" name="blueprint[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="file-input" multiple onchange="previewOptionalFile(this, 'blueprint')">
                            <label for="modal_blueprint_0" class="file-upload-label optional-upload-btn" id="blueprint_upload_label_0">
                                <i class="fi fi-rr-plus"></i>
                                <span>Add Blueprint Images</span>
                            </label>
                            <div class="file-previews-container" id="blueprint_previews_container_0"></div>
                        </div>
                    </div>
                    <small class="form-hint">Accepted: PDF, DOC, DOCX, JPG, JPEG, PNG (Max 10MB each, up to 10 files)</small>
                    <div class="error-message hidden" id="error_blueprint"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fi fi-rr-file"></i>
                        <span>Desired Design Images (Optional)</span>
                    </label>
                    <div id="modal_desired_design_upload_container">
                        <div class="file-upload-wrapper">
                            <input type="file" id="modal_desired_design_0" name="desired_design[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="file-input" multiple onchange="previewOptionalFile(this, 'desired_design')">
                            <label for="modal_desired_design_0" class="file-upload-label optional-upload-btn" id="desired_design_upload_label_0">
                                <i class="fi fi-rr-plus"></i>
                                <span>Add Design Images</span>
                            </label>
                            <div class="file-previews-container" id="desired_design_previews_container_0"></div>
                        </div>
                    </div>
                    <small class="form-hint">Accepted: PDF, DOC, DOCX, JPG, JPEG, PNG (Max 10MB each, up to 10 files)</small>
                    <div class="error-message hidden" id="error_desired_design"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fi fi-rr-file"></i>
                        <span>Others (Optional)</span>
                    </label>
                    <div id="modal_others_upload_container">
                        <div class="file-upload-wrapper">
                            <input type="file" id="modal_others_0" name="others[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="file-input" multiple onchange="previewOptionalFile(this, 'others')">
                            <label for="modal_others_0" class="file-upload-label optional-upload-btn" id="others_upload_label_0">
                                <i class="fi fi-rr-plus"></i>
                                <span>Add Other Images</span>
                            </label>
                            <div class="file-previews-container" id="others_previews_container_0"></div>
                        </div>
                    </div>
                    <small class="form-hint">Accepted: PDF, DOC, DOCX, JPG, JPEG, PNG (Max 10MB each, up to 10 files)</small>
                    <div class="error-message hidden" id="error_others"></div>
                </div>

                <!-- Modal Footer -->
            <style>
            .file-upload-wrapper {
                position: relative;
                display: flex;
                flex-direction: column;
                gap: 12px;
            }
            
            /* Required documents style (original) */
            .file-upload-label {
                position: relative;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 140px;
                padding: 20px;
                border: 2px dashed #ddd;
                border-radius: 12px;
                background: #fafbfc;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            .file-upload-label:hover {
                border-color: #f39c12;
                background: #fff8f0;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(243, 156, 18, 0.1);
            }
            .file-upload-label > i {
                font-size: 32px;
                color: #f39c12;
                margin-bottom: 8px;
                transition: all 0.3s ease;
            }
            .file-upload-label:hover > i {
                transform: scale(1.1);
            }
            .file-upload-label > span {
                font-size: 14px;
                color: #555;
                font-weight: 500;
                transition: all 0.3s ease;
            }
            .file-upload-label.has-preview {
                border-color: #28a745;
                background: #f0f9f4;
            }
            .file-upload-label.has-preview > i,
            .file-upload-label.has-preview > span {
                opacity: 0;
                visibility: hidden;
                position: absolute;
            }
            
            /* Optional documents style (new button style) */
            .optional-upload-btn {
                flex-direction: row;
                min-height: auto;
                padding: 16px 24px;
                border: 2px solid #f39c12;
                border-radius: 8px;
                background: #fff;
                gap: 10px;
            }
            .optional-upload-btn:hover {
                background: #fff8f0;
                border-color: #e68a00;
            }
            .optional-upload-btn > i {
                font-size: 20px;
                color: #f39c12;
                margin-bottom: 0;
            }
            .optional-upload-btn > span {
                font-size: 16px;
                color: #f39c12;
                font-weight: 600;
            }
            .optional-upload-btn:hover > i {
                transform: scale(1);
            }
            
            /* Preview container for optional documents */
            .file-previews-container {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                margin-top: 12px;
            }
            .file-previews-container .file-preview-container {
                position: relative;
                display: inline-block;
                padding: 10px;
                background: #fff;
                border-radius: 12px;
                border: 2px solid #28a745;
                box-shadow: 0 4px 16px rgba(40, 167, 69, 0.15);
                width: fit-content;
                animation: fadeInScale 0.3s ease;
            }
            
            /* Preview container for required documents (inside label) */
            .file-upload-label > .file-preview-container {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                display: none;
                padding: 10px;
                background: #fff;
                border-radius: 12px;
                border: 2px solid #28a745;
                box-shadow: 0 4px 16px rgba(40, 167, 69, 0.15);
            }
            .file-upload-label > .file-preview-container:not(.hidden) {
                display: block;
                animation: fadeInScaleCenter 0.3s ease;
            }
            
            @keyframes fadeInScale {
                from {
                    opacity: 0;
                    transform: scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }
            @keyframes fadeInScaleCenter {
                from {
                    opacity: 0;
                    transform: translate(-50%, -50%) scale(0.9);
                }
                to {
                    opacity: 1;
                    transform: translate(-50%, -50%) scale(1);
                }
            }
            .file-preview-display {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                min-width: 110px;
                min-height: 110px;
                max-width: 110px;
                max-height: 110px;
            }
            .file-preview-display img {
                max-width: 100%;
                max-height: 100%;
                width: auto;
                height: auto;
                border-radius: 8px;
                object-fit: contain;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            }
            .file-preview-display span {
                padding: 16px;
                font-size: 12px;
                color: #666;
                word-break: break-word;
                text-align: center;
                line-height: 1.4;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f8f9fa;
                border-radius: 6px;
            }
            .remove-file-icon-btn {
                position: absolute;
                top: -12px;
                right: -12px;
                width: 36px;
                height: 36px;
                border-radius: 50%;
                background: #ff4757;
                color: #fff;
                border: 3px solid #fff;
                cursor: pointer;
                font-size: 16px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s ease;
                box-shadow: 0 3px 10px rgba(255, 71, 87, 0.4);
                outline: none;
                padding: 0;
                z-index: 10;
            }
            .remove-file-icon-btn:hover, .remove-file-icon-btn:focus {
                background: #ff3838;
                transform: scale(1.15);
                box-shadow: 0 5px 15px rgba(255, 71, 87, 0.5);
            }
            .remove-file-icon-btn:active {
                transform: scale(0.95);
            }
            .remove-file-icon-btn i {
                pointer-events: none;
            }
            </style>
            <script>
            // Clean all formatted values before form submission
            function cleanFormValues() {
                const budgetMin = document.getElementById('modal_budget_range_min');
                const budgetMax = document.getElementById('modal_budget_range_max');
                const lotSize = document.getElementById('modal_lot_size');
                const floorArea = document.getElementById('modal_floor_area');
                
                // Remove commas before submitting
                budgetMin.value = budgetMin.value.replace(/,/g, '');
                budgetMax.value = budgetMax.value.replace(/,/g, '');
                lotSize.value = lotSize.value.replace(/,/g, '');
                floorArea.value = floorArea.value.replace(/,/g, '');
            }

            // Format budget input with commas
            function formatBudgetInput(input) {
                // Remove all non-digit characters
                let value = input.value.replace(/\D/g, '');
                
                // Format with commas
                if (value) {
                    value = parseInt(value, 10).toLocaleString('en-US');
                }
                
                // Update the display value
                input.value = value;
            }

            // Format number input with commas
            function formatNumberInput(input) {
                // Remove all non-digit characters
                let value = input.value.replace(/\D/g, '');
                
                // Format with commas
                if (value) {
                    value = parseInt(value, 10).toLocaleString('en-US');
                }
                
                // Update the display value
                input.value = value;
            }

            // Preview and remove for Optional Documents (Multiple Files)
            let fileAccumulators = {
                'modal_blueprint_0': [],
                'modal_desired_design_0': [],
                'modal_others_0': []
            };

            // Constants for file validation (matching mobile app)
            const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB in bytes
            const MAX_FILES_PER_CATEGORY = 10;

            // Validate file size
            function validateFileSize(file) {
                if (file.size > MAX_FILE_SIZE) {
                    const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    alert('File "' + file.name + '" exceeds the 10MB limit.\n\nFile size: ' + fileSizeMB + 'MB\nMaximum allowed: 10MB\n\nPlease choose a smaller file.');
                    return false;
                }
                return true;
            }

            function previewOptionalFile(input, group) {
                const wrapper = input.closest('.file-upload-wrapper');
                const previewsContainer = wrapper.querySelector('.file-previews-container');
                
                // Get the input ID to track files for this specific input
                const inputId = input.id;
                
                // Initialize accumulator if needed
                if (!fileAccumulators[inputId]) {
                    fileAccumulators[inputId] = [];
                }
                
                // Check file limit before adding new files
                if (input.files && input.files.length > 0) {
                    const currentCount = fileAccumulators[inputId].length;
                    const newFilesCount = input.files.length;
                    
                    if (currentCount >= MAX_FILES_PER_CATEGORY) {
                        alert('You can only upload up to ' + MAX_FILES_PER_CATEGORY + ' files per category.\n\nCurrent files: ' + currentCount);
                        input.value = ''; // Clear the input
                        return;
                    }
                    
                    if (currentCount + newFilesCount > MAX_FILES_PER_CATEGORY) {
                        alert('Adding ' + newFilesCount + ' file(s) would exceed the limit.\n\nCurrent files: ' + currentCount + '\nMaximum allowed: ' + MAX_FILES_PER_CATEGORY + '\n\nYou can add ' + (MAX_FILES_PER_CATEGORY - currentCount) + ' more file(s).');
                        input.value = ''; // Clear the input
                        return;
                    }
                }
                
                // Add new files to accumulator with validation
                if (input.files && input.files.length > 0) {
                    let validFiles = [];
                    let invalidFiles = [];
                    
                    Array.from(input.files).forEach(file => {
                        // Validate file size
                        if (validateFileSize(file)) {
                            validFiles.push(file);
                        } else {
                            invalidFiles.push(file.name);
                        }
                    });
                    
                    // Only add valid files
                    validFiles.forEach(file => {
                        fileAccumulators[inputId].push(file);
                    });
                    
                    // If some files were invalid, clear the input
                    if (invalidFiles.length > 0) {
                        input.value = '';
                    }
                }
                
                // Update the input with all accumulated files
                const dt = new DataTransfer();
                fileAccumulators[inputId].forEach(file => {
                    dt.items.add(file);
                });
                input.files = dt.files;
                
                // Clear and rebuild all previews
                previewsContainer.innerHTML = '';
                
                fileAccumulators[inputId].forEach((file, index) => {
                    const previewContainer = document.createElement('div');
                    previewContainer.className = 'file-preview-container';
                    previewContainer.setAttribute('data-file-index', index);
                    
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'file-preview-display';
                    
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewDiv.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewDiv.innerHTML = '<span>' + file.name + '</span>';
                    }
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'remove-file-icon-btn';
                    removeBtn.setAttribute('data-file-index', index);
                    removeBtn.setAttribute('title', 'Delete file');
                    removeBtn.innerHTML = '<i class="fi fi-rr-trash"></i>';
                    removeBtn.onclick = function() {
                        removeOptionalFileByIndex(input, index);
                    };
                    
                    previewContainer.appendChild(previewDiv);
                    previewContainer.appendChild(removeBtn);
                    previewsContainer.appendChild(previewContainer);
                });
            }

            function removeOptionalFileByIndex(input, fileIndex) {
                const inputId = input.id;
                
                // Remove file from accumulator
                if (fileAccumulators[inputId]) {
                    fileAccumulators[inputId].splice(fileIndex, 1);
                }
                
                // Update the input with remaining files
                const dt = new DataTransfer();
                fileAccumulators[inputId].forEach(file => {
                    dt.items.add(file);
                });
                input.files = dt.files;
                
                // Refresh previews
                const wrapper = input.closest('.file-upload-wrapper');
                const previewsContainer = wrapper.querySelector('.file-previews-container');
                previewsContainer.innerHTML = '';
                
                fileAccumulators[inputId].forEach((file, index) => {
                    const previewContainer = document.createElement('div');
                    previewContainer.className = 'file-preview-container';
                    previewContainer.setAttribute('data-file-index', index);
                    
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'file-preview-display';
                    
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewDiv.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewDiv.innerHTML = '<span>' + file.name + '</span>';
                    }
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'remove-file-icon-btn';
                    removeBtn.setAttribute('data-file-index', index);
                    removeBtn.setAttribute('title', 'Delete file');
                    removeBtn.innerHTML = '<i class="fi fi-rr-trash"></i>';
                    removeBtn.onclick = function() {
                        removeOptionalFileByIndex(input, index);
                    };
                    
                    previewContainer.appendChild(previewDiv);
                    previewContainer.appendChild(removeBtn);
                    previewsContainer.appendChild(previewContainer);
                });
            }

            // Preview and remove for Required Documents
            function previewRequiredFile(input, group) {
                const wrapper = input.closest('.file-upload-wrapper');
                const uploadLabel = wrapper.querySelector('.file-upload-label');
                const previewContainer = wrapper.querySelector('.file-preview-container');
                const previewDiv = wrapper.querySelector('.file-preview-display');
                previewDiv.innerHTML = '';
                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    
                    // Validate file size for required documents
                    if (!validateFileSize(file)) {
                        input.value = ''; // Clear the input
                        previewDiv.innerHTML = '';
                        previewContainer.classList.add('hidden');
                        uploadLabel.classList.remove('has-preview');
                        return;
                    }
                    
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewDiv.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewDiv.innerHTML = '<span>' + file.name + '</span>';
                    }
                    previewContainer.classList.remove('hidden');
                    uploadLabel.classList.add('has-preview');
                } else {
                    previewDiv.innerHTML = '';
                    previewContainer.classList.add('hidden');
                    uploadLabel.classList.remove('has-preview');
                }
            }

            function removeRequiredFile(btn, group) {
                const wrapper = btn.closest('.file-upload-wrapper');
                const uploadLabel = wrapper.querySelector('.file-upload-label');
                const input = wrapper.querySelector('input[type="file"]');
                const previewDiv = wrapper.querySelector('.file-preview-display');
                const previewContainer = wrapper.querySelector('.file-preview-container');
                input.value = '';
                previewDiv.innerHTML = '';
                previewContainer.classList.add('hidden');
                uploadLabel.classList.remove('has-preview');
            }
            </script>
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

