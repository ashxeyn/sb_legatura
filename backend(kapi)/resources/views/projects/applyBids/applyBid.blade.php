<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Bid - {{ $project->project_title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .form-card {
            background-color: #fff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .project-info {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .project-info label {
            font-size: 14px;
            color: #666;
            display: block;
            margin-bottom: 5px;
        }

        .project-info .project-type {
            color: #ff6b35;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            font-family: inherit;
            transition: border-color 0.2s;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff6b35;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #999;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .upload-section {
            margin-bottom: 30px;
        }

        .upload-section label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .upload-area {
            border: 2px dashed #ff6b35;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            background-color: #fff;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .upload-area:hover {
            background-color: #fff5f2;
        }

        .upload-area.dragover {
            background-color: #ffe8e0;
            border-color: #e55a2b;
        }

        .upload-icon {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 10px;
        }

        .upload-text {
            color: #999;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .upload-hint {
            color: #999;
            font-size: 12px;
            margin-top: 10px;
        }

        .upload-area input[type="file"] {
            display: none;
        }

        .file-list {
            margin-top: 15px;
        }

        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 8px;
        }

        .file-item-name {
            flex: 1;
            font-size: 14px;
            color: #333;
        }

        .file-item-remove {
            color: #dc3545;
            cursor: pointer;
            font-size: 18px;
            padding: 0 5px;
        }

        .file-item-remove:hover {
            color: #c82333;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
        }

        .btn-cancel {
            background-color: #e9ecef;
            color: #333;
        }

        .btn-cancel:hover {
            background-color: #dee2e6;
        }

        .btn-submit {
            background-color: #ff6b35;
            color: white;
        }

        .btn-submit:hover {
            background-color: #e55a2b;
            transform: scale(1.02);
        }

        .btn-submit:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }

        .success-message {
            color: #28a745;
            font-size: 14px;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px 10px;
            }

            .form-card {
                padding: 25px;
            }

            .form-title {
                font-size: 24px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">
            <h1 class="form-title">Apply for Bid</h1>

            <div class="project-info">
                <label>Project:</label>
                <div>
                    <span class="project-type">{{ $project->property_type }}</span>
                    <span class="project-type" style="margin-left: 5px;">{{ $project->contractorType->type_name ?? 'N/A' }}</span>
                    @if($project->project_title)
                        <div style="margin-top: 5px; font-weight: 500; color: #333;">{{ $project->project_title }}</div>
                    @endif
                </div>
            </div>

            @if(session('error'))
                <div class="error-message" style="margin-bottom: 20px; padding: 10px; background-color: #f8d7da; border-radius: 6px;">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="success-message" style="margin-bottom: 20px; padding: 10px; background-color: #d4edda; border-radius: 6px;">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('contractor.bid.submit', $project->project_id) }}" method="POST" enctype="multipart/form-data" id="bidForm">
                @csrf

                <div class="form-group">
                    <label for="contractor_id">Contractor:</label>
                    <select id="contractor_id" 
                            name="contractor_id" 
                            required
                            style="width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 6px; font-size: 16px; font-family: inherit; background-color: white; cursor: pointer;">
                        <option value="">Select Contractor</option>
                        @if($contractor)
                            @php
                                $contractorTypeName = $contractor->contractorType->type_name ?? 'N/A';
                                $otherType = ($contractor->type_id == 9 && $contractor->contractor_type_other) ? ' (' . $contractor->contractor_type_other . ')' : '';
                            @endphp
                            <option value="{{ $contractor->contractor_id }}" {{ old('contractor_id') == $contractor->contractor_id || !old('contractor_id') ? 'selected' : '' }}>
                                {{ $contractor->company_name }} - {{ $contractorTypeName }}{{ $otherType }} (You)
                            </option>
                        @endif
                        @foreach($contractors as $cont)
                            @if(!$contractor || $cont->contractor_id != $contractor->contractor_id)
                                @php
                                    $contTypeName = $cont->contractorType->type_name ?? 'N/A';
                                    $contOtherType = ($cont->type_id == 9 && $cont->contractor_type_other) ? ' (' . $cont->contractor_type_other . ')' : '';
                                @endphp
                                <option value="{{ $cont->contractor_id }}" {{ old('contractor_id') == $cont->contractor_id ? 'selected' : '' }}>
                                    {{ $cont->company_name }} - {{ $contTypeName }}{{ $contOtherType }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @if($contractors->isEmpty())
                        <div class="error-message" style="margin-top: 5px;">
                            No contractors available at this time. Please contact the administrator.
                        </div>
                    @endif
                    @error('contractor_id')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="proposed_cost">Proposed cost (PHP):</label>
                    <input type="number" 
                           id="proposed_cost" 
                           name="proposed_cost" 
                           placeholder="Proposed cost (PHP)" 
                           step="0.01" 
                           min="0" 
                           required
                           value="{{ old('proposed_cost') }}">
                    @error('proposed_cost')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="estimated_timeline">Estimated timeline:</label>
                    <input type="number" 
                           id="estimated_timeline" 
                           name="estimated_timeline" 
                           placeholder="In Months" 
                           min="1" 
                           required
                           value="{{ old('estimated_timeline') }}">
                    @error('estimated_timeline')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="contractor_notes">Message to client:</label>
                    <textarea id="contractor_notes" 
                              name="contractor_notes" 
                              placeholder="Write a compelling message to the client. Tell them about your expertise and why you're a great fit." 
                              rows="5">{{ old('contractor_notes') }}</textarea>
                    @error('contractor_notes')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="upload-section">
                    <label>Upload Supporting Documents:</label>
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-icon">☁️</div>
                        <div class="upload-text">Upload image or file</div>
                        <input type="file" 
                               id="supporting_documents" 
                               name="supporting_documents[]" 
                               multiple 
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip">
                        <div class="upload-hint">e.g., document, photos, certificates, permits, etc.</div>
                    </div>
                    <div class="file-list" id="fileList"></div>
                    @error('supporting_documents.*')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-actions">
                    <a href="{{ route('contractor.project.details', $project->project_id) }}" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-submit" id="submitBtn">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('supporting_documents');
        const fileList = document.getElementById('fileList');
        const submitBtn = document.getElementById('submitBtn');
        const bidForm = document.getElementById('bidForm');
        let selectedFiles = [];

        // Click on upload area to trigger file input
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });

        // Handle file selection
        fileInput.addEventListener('change', (e) => {
            handleFiles(Array.from(e.target.files));
        });

        // Drag and drop functionality
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = Array.from(e.dataTransfer.files);
            handleFiles(files);
        });

        // Handle files
        function handleFiles(files) {
            files.forEach(file => {
                // Validate file type
                const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/jpg', 'image/png', 'application/zip'];
                if (!allowedTypes.includes(file.type)) {
                    alert(`${file.name} is not a valid file type. Please upload PDF, DOC, DOCX, JPG, PNG, or ZIP files.`);
                    return;
                }

                // Validate file size (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    alert(`${file.name} is too large. Maximum file size is 10MB.`);
                    return;
                }

                // Add to selected files if not already added
                if (!selectedFiles.find(f => f.name === file.name && f.size === file.size)) {
                    selectedFiles.push(file);
                }
            });

            updateFileList();
            updateFileInput();
        }

        // Update file list display
        function updateFileList() {
            fileList.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <span class="file-item-name">${file.name}</span>
                    <span class="file-item-remove" onclick="removeFile(${index})">×</span>
                `;
                fileList.appendChild(fileItem);
            });
        }

        // Remove file
        function removeFile(index) {
            selectedFiles.splice(index, 1);
            updateFileList();
            updateFileInput();
        }

        // Update file input with selected files
        function updateFileInput() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            fileInput.files = dataTransfer.files;
        }

        // Form submission
        bidForm.addEventListener('submit', (e) => {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
        });
    </script>
</body>
</html>

