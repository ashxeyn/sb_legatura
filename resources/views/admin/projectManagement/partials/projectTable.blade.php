<div class="overflow-x-auto" id="projectsTableWrapper">
  <table class="w-full table-fixed">
    <thead>
      <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[18%]">Property Owner</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[18%]">Project Title</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[16%]">Contractor</th>
        <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[12%]">Verification</th>
        <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[12%]">Progress</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[12%]">Date</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[12%]">Action</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200" id="projectsTableBody">
      @forelse($projects as $project)
      <tr class="hover:bg-indigo-50/60 transition-colors">
        <td class="px-2.5 py-2.5">
          <div class="flex items-center gap-1.5">
            @if($project->owner_profile_pic)
                <img src="{{ asset('storage/' . $project->owner_profile_pic) }}" alt="Owner" class="w-6 h-6 rounded-full object-cover shadow flex-shrink-0">
            @else
                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center text-white text-[9px] font-bold shadow flex-shrink-0">
                {{ substr($project->owner_first_name ?? 'P', 0, 1) . substr($project->owner_last_name ?? 'O', 0, 1) }}
                </div>
            @endif
            <div class="min-w-0">
              <div class="font-medium text-gray-800 text-xs truncate max-w-[120px]" title="{{ ($project->owner_first_name ?? '') . ' ' . ($project->owner_last_name ?? '') }}">{{ ($project->owner_first_name ?? '') . ' ' . ($project->owner_last_name ?? 'N/A') }}</div>
              <div class="text-[10px] text-gray-500 truncate max-w-[120px]">ID: {{ $project->project_id }}</div>
            </div>
          </div>
        </td>
        <td class="px-2.5 py-2.5">
          <div class="font-medium text-gray-800 text-xs truncate max-w-[130px]" title="{{ $project->project_title ?? 'N/A' }}">{{ $project->project_title ?? 'N/A' }}</div>
        </td>
        <td class="px-2.5 py-2.5 text-xs text-gray-700 truncate max-w-[110px]" title="{{ $project->contractor_company ?? 'Not Assigned' }}">
          {{ $project->contractor_company ?? 'Not Assigned' }}
        </td>
        <td class="px-2.5 py-2.5 text-center">
          @php
            $verificationStatus = $project->project_post_status ?? 'under_review';
            $verificationClass = match($verificationStatus) {
                'approved' => 'bg-green-100 text-green-700',
                'rejected' => 'bg-red-100 text-red-700',
                'under_review' => 'bg-yellow-100 text-yellow-700',
                default => 'bg-gray-100 text-gray-700'
            };
            $verificationLabel = match($verificationStatus) {
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'under_review' => 'Pending',
                default => ucfirst(str_replace('_', ' ', $verificationStatus))
            };
          @endphp
          <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $verificationClass }}">
            {{ $verificationLabel }}
          </span>
        </td>
        <td class="px-2.5 py-2.5 text-center">
          @php
            $progressStatus = $project->project_status ?? 'open';
            $progressClass = match($progressStatus) {
                'completed' => 'bg-green-100 text-green-700',
                'in_progress' => 'bg-blue-100 text-blue-700',
                'open' => 'bg-indigo-100 text-indigo-700',
                'bidding_closed' => 'bg-amber-100 text-amber-700',
                'halt' => 'bg-red-100 text-red-700',
                'terminated' => 'bg-gray-100 text-gray-700',
                'deleted' => 'bg-red-100 text-red-700',
                default => 'bg-gray-100 text-gray-700'
            };
            $progressLabel = match($progressStatus) {
                'in_progress' => 'Ongoing',
                'open' => 'Bidding',
                'bidding_closed' => 'Closed',
                'halt' => 'Halted',
                default => ucfirst(str_replace('_', ' ', $progressStatus))
            };
          @endphp
          <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $progressClass }}">
            {{ $progressLabel }}
          </span>
        </td>
        <td class="px-2.5 py-2.5 whitespace-nowrap text-xs text-gray-600">
          {{ $project->submitted_at ? \Carbon\Carbon::parse($project->submitted_at)->format('m/d/y') : 'N/A' }}
        </td>
        <td class="px-2.5 py-2.5 whitespace-nowrap">
          <div class="flex items-center gap-1">
            <button class="action-btn view-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-indigo-100 hover:shadow-sm hover:border-indigo-300 hover:-translate-y-0.5 transition-all" title="View" data-id="{{ $project->project_id }}">
              <i class="fi fi-rr-eye text-[13px] leading-none"></i>
            </button>
            <button class="action-btn edit-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-orange-200 bg-orange-50 text-orange-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-orange-100 hover:shadow-sm hover:border-orange-300 hover:-translate-y-0.5 transition-all" title="Edit" data-id="{{ $project->project_id }}">
              <i class="fi fi-rr-pencil text-[12px] leading-none"></i>
            </button>
            @if($project->project_status === 'deleted')
            <button class="action-btn restore-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-green-200 bg-green-50 text-green-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-green-100 hover:shadow-sm hover:border-green-300 hover:-translate-y-0.5 transition-all" title="Restore" data-id="{{ $project->project_id }}">
              <i class="fi fi-rr-refresh text-[13px] leading-none"></i>
            </button>
            @else
            <button class="action-btn delete-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-red-200 bg-red-50 text-red-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-red-100 hover:shadow-sm hover:border-red-300 hover:-translate-y-0.5 transition-all" title="Delete" data-id="{{ $project->project_id }}">
              <i class="fi fi-rr-trash text-[13px] leading-none"></i>
            </button>
            @endif
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
          <i class="fi fi-rr-folder-open text-3xl block mb-2"></i>
          <p class="text-base font-medium text-gray-500">No projects found</p>
          <p class="text-xs mt-1">Try adjusting your search or filter criteria.</p>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>

  @if($projects->hasPages())
  <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
    <p class="text-xs text-gray-500">
      Showing <strong>{{ $projects->firstItem() }}</strong>–<strong>{{ $projects->lastItem() }}</strong>
      of <strong>{{ $projects->total() }}</strong> results
    </p>
    <div class="flex items-center gap-1">
      @if($projects->onFirstPage())
        <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
      @else
        <a href="{{ $projects->previousPageUrl() }}" class="project-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
      @endif

      @foreach($projects->getUrlRange(max(1, $projects->currentPage()-2), min($projects->lastPage(), $projects->currentPage()+2)) as $page => $url)
        @if($page == $projects->currentPage())
          <span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">{{ $page }}</span>
        @else
          <a href="{{ $url }}" class="project-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
        @endif
      @endforeach

      @if($projects->hasMorePages())
        <a href="{{ $projects->nextPageUrl() }}" class="project-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
      @else
        <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
      @endif
    </div>
  </div>
  @else
  <div class="px-4 py-3 border-t border-gray-200">
    <p class="text-xs text-gray-500">
      Showing <strong>{{ $projects->total() }}</strong> result(s)
    </p>
  </div>
  @endif
</div>
