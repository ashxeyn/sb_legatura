@isset($project)
<div class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl relative transform transition-all duration-300 scale-100 max-h-[90vh] overflow-hidden flex flex-col" data-location="{{ $project->project_location ?? '' }}" data-project-id="{{ $project->project_id }}">
      <!-- Header -->
  <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 flex-shrink-0">
    <div>
      <h2 class="text-lg font-bold text-gray-900">Edit Project Information</h2>
      <p class="text-xs text-gray-500 mt-0.5">Update project details and manage contractor assignment</p>
    </div>
    <button onclick="hideEditProjectModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition text-rose-500 hover:text-rose-600">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>

  <!-- Content -->
  <div class="p-6 space-y-6 overflow-y-auto flex-1">

    <!-- Owner Information -->
    <div class="space-y-3">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <h3 class="text-base font-bold text-gray-900">Owner Information</h3>
      </div>
      <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
        <span class="text-xs font-semibold text-gray-600">Owner Name</span>
        <p class="text-sm text-gray-900 font-medium mt-1">{{ $project->owner_name ?? '—' }}</p>
      </div>
    </div>

    <!-- Project Details Section -->
    <div class="space-y-4">
      <div class="flex items-center gap-2 mb-3">
        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <h3 class="text-base font-bold text-gray-900">Project Details</h3>
      </div>

      <div class="grid grid-cols-1 gap-4">
        <div>
          <label class="block text-sm font-semibold text-gray-900 mb-2">Project Title</label>
          <input type="text" id="editProjectTitle" value="{{ $project->project_title ?? '' }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
          <p class="text-xs text-red-600 mt-1 hidden" id="error-project_title"></p>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-900 mb-2">Project Description</label>
          <textarea id="editProjectDescription" rows="4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none">{{ $project->project_description ?? '' }}</textarea>
          <p class="text-xs text-red-600 mt-1 hidden" id="error-project_description"></p>
        </div>

        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Property Type</label>
            <select id="editPropertyType" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
              <option value="Residential" {{ ($project->property_type ?? '') == 'Residential' ? 'selected' : '' }}>Residential</option>
              <option value="Commercial" {{ ($project->property_type ?? '') == 'Commercial' ? 'selected' : '' }}>Commercial</option>
              <option value="Industrial" {{ ($project->property_type ?? '') == 'Industrial' ? 'selected' : '' }}>Industrial</option>
              <option value="Agricultural" {{ ($project->property_type ?? '') == 'Agricultural' ? 'selected' : '' }}>Agricultural</option>
            </select>
            <p class="text-xs text-red-600 mt-1 hidden" id="error-property_type"></p>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Lot Size (m²)</label>
            <input type="number" id="editLotSize" value="{{ $project->lot_size ?? '' }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
            <p class="text-xs text-red-600 mt-1 hidden" id="error-lot_size"></p>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Floor Area (m²)</label>
            <input type="number" id="editFloorArea" value="{{ $project->floor_area ?? '' }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
            <p class="text-xs text-red-600 mt-1 hidden" id="error-floor_area"></p>
          </div>
        </div>

        <div class="space-y-4">
          <label class="block text-sm font-semibold text-gray-900 mb-2">Project Location</label>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs text-gray-600 mb-1">Province</label>
              <select id="editProvince" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                <option value="">Select Province</option>
              </select>
            </div>

            <div>
              <label class="block text-xs text-gray-600 mb-1">City/Municipality</label>
              <select id="editCity" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" disabled>
                <option value="">Select City</option>
              </select>
            </div>

            <div>
              <label class="block text-xs text-gray-600 mb-1">Barangay</label>
              <select id="editBarangay" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" disabled>
                <option value="">Select Barangay</option>
              </select>
            </div>

            <div>
              <label class="block text-xs text-gray-600 mb-1">Postal Code</label>
              <input type="text" id="editPostalCode" placeholder="e.g., 1000" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
            </div>
          </div>

          <div>
            <label class="block text-xs text-gray-600 mb-1">Street Address</label>
            <input type="text" id="editStreet" placeholder="e.g., 123 Main Street, Subdivision Name" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
          </div>
          <p class="text-xs text-red-600 mt-1 hidden" id="error-project_location"></p>
        </div>
      </div>
    </div>

    <!-- Owner Information -->
    <div class="space-y-3">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
        <h3 class="text-base font-bold text-gray-900">Project Files</h3>
      </div>

      @if($project->files && count($project->files) > 0)
        <div class="space-y-2">
          @foreach($project->files as $file)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
              <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span class="text-sm text-gray-700">{{ ucfirst(str_replace('_', ' ', $file->file_type)) }} - {{ basename($file->file_path) }}</span>
              </div>
              <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="text-orange-600 hover:text-orange-700 text-sm font-medium">
                View
              </a>
            </div>
          @endforeach
        </div>
      @else
        <p class="text-sm text-gray-500">No files attached to this project</p>
      @endif
    </div>

    <!-- Current Contractor Section -->
    <div class="space-y-3">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <h3 class="text-base font-bold text-gray-900">Current Contractor</h3>
      </div>

      @if($project->selected_contractor_id)
        <div class="bg-gradient-to-r from-blue-50 to-white p-4 rounded-xl border border-blue-200">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <span class="text-xs font-semibold text-gray-600">Company Name</span>
              <p class="text-sm text-gray-900 font-medium mt-1">{{ $project->company_name ?? '—' }}</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">Contractor Type</span>
              <p class="text-sm text-gray-900 font-medium mt-1">{{ $project->contractor_type ?? '—' }}</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">Phone Number</span>
              <p class="text-sm text-gray-900 font-medium mt-1">{{ $project->contractor_phone ?? '—' }}</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">Email</span>
              <p class="text-sm text-gray-900 font-medium mt-1">{{ $project->contractor_email ?? '—' }}</p>
            </div>
            <div class="col-span-2">
              <span class="text-xs font-semibold text-gray-600">Business Address</span>
              <p class="text-sm text-gray-900 font-medium mt-1">{{ $project->contractor_address ?? '—' }}</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">PCAB License</span>
              <p class="text-sm text-gray-900 font-medium mt-1">{{ $project->pcab_license_no ?? '—' }}</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">PCAB Category</span>
              <p class="text-sm text-gray-900 font-medium mt-1">{{ $project->picab_category ?? '—' }}</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">Business Permit</span>
              <p class="text-sm text-gray-900 font-medium mt-1">{{ $project->business_permit ?? '—' }}</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">Business Permit City</span>
              <p class="text-sm text-gray-900 font-medium mt-1">{{ $project->business_permit_city ?? '—' }}</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">TIN</span>
              <p class="text-sm text-gray-900 font-medium mt-1">{{ $project->tin_no ?? '—' }}</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">Years of Experience</span>
              <p class="text-sm text-gray-900 font-medium mt-1">{{ $project->years_of_experience ?? '—' }} years</p>
            </div>
          </div>
        </div>
      @else
        <p class="text-sm text-gray-500">No contractor assigned yet</p>
      @endif
    </div>

    <!-- Change Contractor Section (Only for ongoing, bidding_closed, halt statuses) -->
    @if(in_array($project->project_status ?? '', ['in_progress', 'bidding_closed', 'halt']))
    <div class="space-y-3">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        <h3 class="text-base font-bold text-gray-900">Change Contractor</h3>
      </div>

      @if($project->alternative_contractors && count($project->alternative_contractors) > 0)
        <div>
          <label class="block text-sm font-semibold text-gray-900 mb-2">Select New Contractor <span class="text-gray-500 font-normal">(Optional)</span></label>
          <select id="editContractorSelect" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
            <option value="">— Keep Current Contractor —</option>
            @foreach($project->alternative_contractors as $contractor)
              <option value="{{ $contractor->contractor_id }}"
                      data-bid-id="{{ $contractor->bid_id }}"
                      data-company="{{ $contractor->company_name }}"
                      data-type="{{ $contractor->contractor_type }}"
                      data-phone="{{ $contractor->phone_number }}"
                      data-email="{{ $contractor->email }}"
                      data-address="{{ $contractor->address }}"
                      data-pcab="{{ $contractor->pcab_license_no }}"
                      data-pcab-category="{{ $contractor->picab_category }}"
                      data-permit="{{ $contractor->business_permit }}"
                      data-permit-city="{{ $contractor->business_permit_city }}"
                      data-tin="{{ $contractor->tin_no }}"
                      data-experience="{{ $contractor->years_of_experience }}"
                      data-bid="{{ $contractor->proposed_cost }}"
                      data-timeline="{{ $contractor->estimated_timeline }}"
                      data-notes="{{ $contractor->contractor_notes }}"
                      data-files='@json($contractor->bid_files)'>
                {{ $contractor->company_name }}
              </option>
            @endforeach
          </select>
        </div>

        <!-- Preview Selected Contractor -->
        <div id="editContractorPreview" class="hidden bg-gradient-to-r from-orange-50 to-white p-4 rounded-xl border border-orange-200">
          <p class="text-xs font-semibold text-orange-700 mb-3">New Contractor Preview:</p>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <span class="text-xs font-semibold text-gray-600">Company Name</span>
              <p class="text-sm text-gray-900 font-medium mt-1" id="previewCompany">—</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">Contractor Type</span>
              <p class="text-sm text-gray-900 font-medium mt-1" id="previewType">—</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">Phone Number</span>
              <p class="text-sm text-gray-900 font-medium mt-1" id="previewPhone">—</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">Email</span>
              <p class="text-sm text-gray-900 font-medium mt-1" id="previewEmail">—</p>
            </div>
            <div class="col-span-2">
              <span class="text-xs font-semibold text-gray-600">Business Address</span>
              <p class="text-sm text-gray-900 font-medium mt-1" id="previewAddress">—</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">PCAB License</span>
              <p class="text-sm text-gray-900 font-medium mt-1" id="previewPCAB">—</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">PCAB Category</span>
              <p class="text-sm text-gray-900 font-medium mt-1" id="previewPCABCategory">—</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">Business Permit</span>
              <p class="text-sm text-gray-900 font-medium mt-1" id="previewPermit">—</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">Business Permit City</span>
              <p class="text-sm text-gray-900 font-medium mt-1" id="previewPermitCity">—</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">TIN</span>
              <p class="text-sm text-gray-900 font-medium mt-1" id="previewTIN">—</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">Years of Experience</span>
              <p class="text-sm text-gray-900 font-medium mt-1" id="previewExperience">—</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">Proposed Cost</span>
              <p class="text-sm text-gray-900 font-medium mt-1" id="previewBid">—</p>
            </div>
          </div>
        </div>

        <!-- Bid Information -->
        <div id="editBidInfo" class="hidden bg-gradient-to-r from-green-50 to-white p-4 rounded-xl border border-green-200 mt-3">
          <p class="text-xs font-semibold text-green-700 mb-3">Bid Information:</p>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <span class="text-xs font-semibold text-gray-600">Proposed Cost</span>
              <p class="text-sm text-gray-900 font-medium mt-1" id="bidCost">—</p>
            </div>
            <div>
              <span class="text-xs font-semibold text-gray-600">Estimated Timeline</span>
              <p class="text-sm text-gray-900 font-medium mt-1" id="bidTimeline">—</p>
            </div>
            <div class="col-span-2">
              <span class="text-xs font-semibold text-gray-600">Contractor Notes</span>
              <p class="text-sm text-gray-900 mt-1" id="bidNotes">—</p>
            </div>
            <div class="col-span-2" id="bidFilesContainer" style="display: none;">
              <span class="text-xs font-semibold text-gray-600">Bid Files</span>
              <div id="bidFilesList" class="space-y-2 mt-2"></div>
            </div>
          </div>
        </div>
      @else
        <p class="text-sm text-gray-500 italic">No alternative contractors available. This project has no other bids submitted.</p>
      @endif
    </div>
    @endif

  </div>

  <!-- Footer -->
  <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3 flex-shrink-0">
    <button onclick="hideEditProjectModal()" class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 transition">
      Cancel
    </button>
    <button onclick="showEditProjectConfirmModal()" class="px-6 py-2.5 text-sm font-semibold rounded-lg bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white transition shadow-md hover:shadow-lg">
      Save Changes
    </button>
  </div>
</div>
@endisset
