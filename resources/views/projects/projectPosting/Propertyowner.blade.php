<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Title - Post Project</title>
    <style>
        body {
        font-family: Arial, sans-serif;
        margin: 20px;
        background-color: #f4f4f4;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        form > div {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        input[type="datetime-local"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        form > div > div {
            margin-bottom: 10px;
        }

        span {
            color: red;
            font-size: 0.9em;
            display: block;
            margin-top: -5px;
        }

        .success-message {
            color: green;
            padding: 10px;
            border: 1px solid green;
            background-color: #e6ffe6;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .error-list {
            color: red;
            padding: 10px;
            border: 1px solid red;
            background-color: #ffe6e6;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .error-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        button[type="submit"] {
            background-color: #5cb85c;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 50%;
            margin-top: 0;
        }

        button[type="submit"]:hover {
            background-color: #4cae4c;
        }

        .cancel-button {
            background-color: #6c757d;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 50%;
            margin-top: 0;
            text-align: center;
            display: block;
            text-decoration: none;
        }

        .cancel-button:hover {
            background-color: #5a6268;
        }
        </style>
</head>
<body>
    <form action="/owner/projects" method="POST" enctype="multipart/form-data">
        @csrf

        <div>
            <label for="project_title">Project Title</label>
            <input type="text" id="project_title" name="project_title" placeholder="Enter project title" required>
            @error('project_title')
                <span>{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="project_description">Project Description</label>
            <textarea id="project_description" name="project_description" placeholder="Enter project description" rows="4" required></textarea>
            @error('project_description')
                <span>{{ $message }}</span>
            @enderror
        </div>

        <div style="border: 1px solid #ddd; border-radius: 4px; padding: 10px; margin-top: 10px;">
            <label>Upload House/Property Photos (Optional)</label>
            <div>
                <input type="file" name="house_photos[]" accept=".jpg,.jpeg,.png,.webp" multiple="multiple" id="house_photos_input">
                <p style="font-size: 0.9em; color: #666; margin-top: 5px;">You can upload multiple photos of your property (e.g., exterior, interior, different angles). Hold Ctrl (or Cmd on Mac) to select multiple files.</p>
                <p id="house_photos_count" style="font-size: 0.85em; color: #5cb85c; margin-top: 5px; display: none;"></p>
                <div id="house_photos_list" style="margin-top: 10px; display: none;"></div>
                @error('house_photos')
                    <span>{{ $message }}</span>
                @enderror
                @error('house_photos.*')
                    <span>{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label>Property Address</label>
            <div>
                <input type="text" name="street_address" placeholder="Street Address / Building / House No." required>
                @error('street_address')
                    <span>{{ $message }}</span>
                @enderror
            </div>
            <div>
                <input type="text" name="city_municipality" placeholder="City / Municipality" required>
                @error('city_municipality')
                    <span>{{ $message }}</span>
                @enderror
            </div>
            <div>
                <input type="text" name="province_state_region" placeholder="Province / State / Region" required>
                @error('province_state_region')
                    <span>{{ $message }}</span>
                @enderror
            </div>
            <div>
                <input type="text" name="postal_code" placeholder="Postal code" required>
                @error('postal_code')
                    <span>{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label>Property Details</label>
            <div>
                <select name="property_type" required>
                    <option value="">Select Property Type</option>
                    <option value="Residential">Residential</option>
                    <option value="Commercial">Commercial</option>
                    <option value="Industrial">Industrial</option>
                    <option value="Agricultural">Agricultural</option>
                </select>
                @error('property_type')
                    <span>{{ $message }}</span>
                @enderror
            </div>
            <div>
                <input type="number" name="lot_size" placeholder="Lot Size (sqm)" min="0" required>
                @error('lot_size')
                    <span>{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label style="margin-top: 10px; margin-bottom: 10px;">Floor Area (sqm)</label>
                <div id="floor_area_container">
                    <div class="floor_area_input" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">
                        <input type="number" name="floor_area[]" placeholder="Floor 1 Area (sqm)" min="0" step="0.01" required style="flex: 1;">
                        <span style="color: #333; font-weight: normal;">Floor 1</span>
                        <button type="button" class="remove_floor" style="display: none; background-color: #dc3545; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">Remove</button>
                    </div>
                </div>
                <button type="button" id="add_floor_btn" style="background-color: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; margin-top: 5px;">+ Add Another Floor</button>
                @error('floor_area')
                    <span>{{ $message }}</span>
                @enderror
                @error('floor_area.*')
                    <span>{{ $message }}</span>
                @enderror
            </div>
            <div style="border: 1px solid #ddd; border-radius: 4px; padding: 10px; margin-top: 10px;">
                <label style="margin-top: 10px; margin-bottom: 10px;">Upload Blueprint (Optional)</label>
                <div>
                    <input type="file" name="blueprint" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.dwg,.dxf">
                    <p style="font-size: 0.9em; color: #666; margin-top: 5px;">Upload blueprint or architectural drawings</p>
                    @error('blueprint')
                        <span>{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div>
            <label>Contractor Type</label>
            <select name="type_id" id="type_id" required>
                <option value="">Select Contractor Type</option>
                @php
                    // Separate "Others" from other types
                    $othersType = null;
                    $otherTypes = [];
                    foreach($types as $type) {
                        if($type->type_name === 'Others' || $type->type_id == 9) {
                            $othersType = $type;
                        } else {
                            $otherTypes[] = $type;
                        }
                    }
                @endphp
                {{-- Display other types first --}}
                @foreach($otherTypes as $type)
                    <option value="{{ $type->type_id }}">{{ $type->type_name }}</option>
                @endforeach
                {{-- Display "Others" at the bottom --}}
                @if($othersType)
                    <option value="{{ $othersType->type_id }}">{{ $othersType->type_name }}</option>
                @endif
            </select>
            @error('type_id')
                <span>{{ $message }}</span>
            @enderror
            
            {{-- Input field for "Others" - hidden by default --}}
            <div id="contractor_type_other_wrapper" style="display: none; margin-top: 10px;">
                <input type="text" name="contractor_type_other" id="contractor_type_other" placeholder="Please specify the contractor type" value="{{ old('contractor_type_other') }}">
                @error('contractor_type_other')
                    <span>{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label>Target timeline</label>
            <div>
                <input type="number" name="timeline_min" placeholder="Min: (e.g. 12 Months)" min="1" required>
                @error('timeline_min')
                    <span>{{ $message }}</span>
                @enderror
            </div>
            <div>
                <input type="number" name="timeline_max" placeholder="Max: (e.g. 24 Months)" min="1" required>
                @error('timeline_max')
                    <span>{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label>Budget</label>
            <div>
                <input type="number" name="budget_range_min" placeholder="Min: (Philippine Peso)" min="0" step="0.01" required>
                @error('budget_range_min')
                    <span>{{ $message }}</span>
                @enderror
            </div>
            <div>
                <input type="number" name="budget_range_max" placeholder="Max: (Philippine Peso)" min="0" step="0.01" required>
                @error('budget_range_max')
                    <span>{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label>Bidding Deadline</label>
            <input type="datetime-local" name="bidding_deadline" placeholder="Set when your bidding will end" required>
            @error('bidding_deadline')
                <span>{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label>Upload Land Title (Required)</label>
            <div>
                <input type="file" name="land_title" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                @error('land_title')
                    <span>{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label>Upload Supporting Documents (Optional)</label>
            <div>
                <input type="file" name="supporting_documents[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip" multiple="multiple" id="supporting_documents_input">
                <p style="font-size: 0.9em; color: #666; margin-top: 5px;">e.g, photos, certificates, permits, etc. Hold Ctrl (or Cmd on Mac) to select multiple files.</p>
                <p id="supporting_documents_count" style="font-size: 0.85em; color: #5cb85c; margin-top: 5px; display: none;"></p>
                <div id="supporting_documents_list" style="margin-top: 10px; display: none;"></div>
                @error('supporting_documents')
                    <span>{{ $message }}</span>
                @enderror
                @error('supporting_documents.*')
                    <span>{{ $message }}</span>
                @enderror
            </div>
        </div>

        <input type="hidden" name="owner_id" value="1"> 

        @if(session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="error-list">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="button-group">
            <a href="/" class="cancel-button">Cancel</a> 
            <button type="submit">Post</button>
        </div>
        
    </form>

    <script>
      
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('type_id');
            const otherWrapper = document.getElementById('contractor_type_other_wrapper');
            const otherInput = document.getElementById('contractor_type_other');
            
          
            function toggleOtherInput() {
                const selectedValue = typeSelect.value;
                
                if (selectedValue === '9' || typeSelect.options[typeSelect.selectedIndex].text === 'Others') {
                    otherWrapper.style.display = 'block';
                    otherInput.setAttribute('required', 'required');
                } else {
                    otherWrapper.style.display = 'none';
                    otherInput.removeAttribute('required');
                    otherInput.value = ''; 
                }
            }
            
         
            typeSelect.addEventListener('change', toggleOtherInput);
            
         
            toggleOtherInput();
            
         
            const floorAreaContainer = document.getElementById('floor_area_container');
            const addFloorBtn = document.getElementById('add_floor_btn');
            let floorCount = 1;
            
           
            function updateFloorLabels() {
                const floorInputs = floorAreaContainer.querySelectorAll('.floor_area_input');
                floorInputs.forEach((inputDiv, index) => {
                    const floorNumber = index + 1;
                    const span = inputDiv.querySelector('span');
                    const input = inputDiv.querySelector('input');
                    const removeBtn = inputDiv.querySelector('.remove_floor');
                    
                    span.textContent = `Floor ${floorNumber}`;
                    input.placeholder = `Floor ${floorNumber} Area (sqm)`;
                    
               
                    if (floorInputs.length > 1) {
                        removeBtn.style.display = 'block';
                    } else {
                        removeBtn.style.display = 'none';
                    }
                });
            }
            
       
            addFloorBtn.addEventListener('click', function() {
                floorCount++;
                const newFloorDiv = document.createElement('div');
                newFloorDiv.className = 'floor_area_input';
                newFloorDiv.style.cssText = 'display: flex; gap: 10px; margin-bottom: 10px; align-items: center;';
                newFloorDiv.innerHTML = `
                    <input type="number" name="floor_area[]" placeholder="Floor ${floorCount} Area (sqm)" min="0" step="0.01" required style="flex: 1;">
                    <span style="color: #333; font-weight: normal;">Floor ${floorCount}</span>
                    <button type="button" class="remove_floor" style="background-color: #dc3545; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">Remove</button>
                `;
                floorAreaContainer.appendChild(newFloorDiv);
                updateFloorLabels();
                
               
                newFloorDiv.querySelector('.remove_floor').addEventListener('click', function() {
                    newFloorDiv.remove();
                    updateFloorLabels();
                });
            });
            
        
            document.querySelectorAll('.remove_floor').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.floor_area_input').remove();
                    updateFloorLabels();
                });
            });
            
        
            updateFloorLabels();
            
       
            const housePhotosInput = document.getElementById('house_photos_input');
            const housePhotosCount = document.getElementById('house_photos_count');
            const housePhotosList = document.getElementById('house_photos_list');
            const supportingDocumentsInput = document.getElementById('supporting_documents_input');
            const supportingDocumentsCount = document.getElementById('supporting_documents_count');
            const supportingDocumentsList = document.getElementById('supporting_documents_list');
            
         
            let selectedHousePhotos = [];
            let selectedSupportingDocuments = [];
            
          
            function updateHousePhotosDisplay() {
                const fileCount = selectedHousePhotos.length;
                if (fileCount > 0) {
                    housePhotosCount.style.display = 'block';
                    housePhotosCount.textContent = `${fileCount} file(s) selected`;
                    
                   
                    housePhotosList.style.display = 'block';
                    housePhotosList.innerHTML = '<div style="font-weight: bold; margin-bottom: 5px; color: #333;">Selected files:</div>' +
                        selectedHousePhotos.map((file, index) => 
                            `<div style="padding: 5px; margin: 3px 0; background-color: #f0f0f0; border-radius: 3px; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.9em;">${file.name}</span>
                                <button type="button" onclick="removeHousePhoto(${index})" style="background-color: #dc3545; color: white; border: none; padding: 3px 8px; border-radius: 3px; cursor: pointer; font-size: 0.85em;">Remove</button>
                            </div>`
                        ).join('');
                } else {
                    housePhotosCount.style.display = 'none';
                    housePhotosList.style.display = 'none';
                }
            }
            
          
            housePhotosInput.addEventListener('change', function() {
                const newFiles = Array.from(this.files);
                
             
                newFiles.forEach(newFile => {
                    const exists = selectedHousePhotos.some(existingFile => 
                        existingFile.name === newFile.name && existingFile.size === newFile.size
                    );
                    if (!exists) {
                        selectedHousePhotos.push(newFile);
                    }
                });
                
            
                const dataTransfer = new DataTransfer();
                selectedHousePhotos.forEach(file => dataTransfer.items.add(file));
                this.files = dataTransfer.files;
                
                updateHousePhotosDisplay();
            });
            
          
            window.removeHousePhoto = function(index) {
                selectedHousePhotos.splice(index, 1);
                const dataTransfer = new DataTransfer();
                selectedHousePhotos.forEach(file => dataTransfer.items.add(file));
                housePhotosInput.files = dataTransfer.files;
                updateHousePhotosDisplay();
            };
            
         
            function updateSupportingDocumentsDisplay() {
                const fileCount = selectedSupportingDocuments.length;
                if (fileCount > 0) {
                    supportingDocumentsCount.style.display = 'block';
                    supportingDocumentsCount.textContent = `${fileCount} file(s) selected`;
                    
                 
                    supportingDocumentsList.style.display = 'block';
                    supportingDocumentsList.innerHTML = '<div style="font-weight: bold; margin-bottom: 5px; color: #333;">Selected files:</div>' +
                        selectedSupportingDocuments.map((file, index) => 
                            `<div style="padding: 5px; margin: 3px 0; background-color: #f0f0f0; border-radius: 3px; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.9em;">${file.name}</span>
                                <button type="button" onclick="removeSupportingDocument(${index})" style="background-color: #dc3545; color: white; border: none; padding: 3px 8px; border-radius: 3px; cursor: pointer; font-size: 0.85em;">Remove</button>
                            </div>`
                        ).join('');
                } else {
                    supportingDocumentsCount.style.display = 'none';
                    supportingDocumentsList.style.display = 'none';
                }
            }
            
         
            supportingDocumentsInput.addEventListener('change', function() {
                const newFiles = Array.from(this.files);
                
              
                newFiles.forEach(newFile => {
                    const exists = selectedSupportingDocuments.some(existingFile => 
                        existingFile.name === newFile.name && existingFile.size === newFile.size
                    );
                    if (!exists) {
                        selectedSupportingDocuments.push(newFile);
                    }
                });
                
           
                const dataTransfer = new DataTransfer();
                selectedSupportingDocuments.forEach(file => dataTransfer.items.add(file));
                this.files = dataTransfer.files;
                
                updateSupportingDocumentsDisplay();
            });
            
           
            window.removeSupportingDocument = function(index) {
                selectedSupportingDocuments.splice(index, 1);
                const dataTransfer = new DataTransfer();
                selectedSupportingDocuments.forEach(file => dataTransfer.items.add(file));
                supportingDocumentsInput.files = dataTransfer.files;
                updateSupportingDocumentsDisplay();
            };
        });
    </script>
</body>
</html>