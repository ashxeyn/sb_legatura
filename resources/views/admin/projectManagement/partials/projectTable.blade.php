<div class="overflow-x-auto">
  <table class="w-full">
    <thead class="bg-gradient-to-r from-orange-50 to-orange-100">
      <tr class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
        <th class="px-6 py-4">Property Owner</th>
        <th class="px-6 py-4">Project Title</th>
        <th class="px-6 py-4">Contractor Company</th>
        <th class="px-6 py-4">Verification Status</th>
        <th class="px-6 py-4">Progress Status</th>
        <th class="px-6 py-4">Date Submitted</th>
        <th class="px-6 py-4 text-center">Action</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200 text-sm" id="projectsTableBody">
      @forelse($projects as $project)
      <tr class="hover:bg-orange-50 transition duration-150 ease-in-out group">
        <td class="px-6 py-4">
          <div class="flex items-center gap-3">
            @if($project->owner_profile_pic)
                <img src="{{ asset('storage/' . $project->owner_profile_pic) }}" alt="Owner" class="w-10 h-10 rounded-full object-cover shadow-md flex-shrink-0">
            @else
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-semibold shadow-md flex-shrink-0">
                {{ substr($project->owner_first_name ?? 'P', 0, 1) . substr($project->owner_last_name ?? 'O', 0, 1) }}
                </div>
            @endif
            <span class="font-medium text-gray-900">{{ ($project->owner_first_name ?? '') . ' ' . ($project->owner_last_name ?? 'N/A') }}</span>
          </div>
        </td>
        <td class="px-6 py-4">
          <div class="text-sm font-medium text-gray-900">{{ $project->project_title ?? 'N/A' }}</div>
          <div class="text-xs text-gray-500">ID: {{ $project->project_id }}</div>
        </td>
        <td class="px-6 py-4">
          <span class="text-sm text-gray-700">{{ $project->contractor_company ?? 'Not Assigned' }}</span>
        </td>
        <td class="px-6 py-4">
          @php
            $verificationStatus = $project->project_post_status ?? 'under_review';
            $verificationClass = match($verificationStatus) {
                'approved' => 'bg-green-100 text-green-700',
                'rejected' => 'bg-red-100 text-red-700',
                'under_review' => 'bg-yellow-100 text-yellow-700',
                default => 'bg-gray-100 text-gray-700'
            };
          @endphp
          <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $verificationClass }}">
            {{ ucfirst(str_replace('_', ' ', $verificationStatus)) }}
          </span>
        </td>
        <td class="px-6 py-4">
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
                'open' => 'In Bidding',
                'bidding_closed' => 'Bidding Closed',
                'halt' => 'Halted',
                default => ucfirst(str_replace('_', ' ', $progressStatus))
            };
          @endphp
          <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $progressClass }}">
            {{ $progressLabel }}
          </span>
        </td>
        <td class="px-6 py-4">
          <div class="text-sm text-gray-600">{{ $project->submitted_at ? \Carbon\Carbon::parse($project->submitted_at)->format('d M, Y') : 'N/A' }}</div>
        </td>
        <td class="px-6 py-4">
          <div class="flex items-center justify-center gap-2">
            <button class="action-btn view-btn p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="View" data-id="{{ $project->project_id }}">
              <i class="fi fi-rr-eye"></i>
            </button>
            <button class="action-btn edit-btn p-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition" title="Edit" data-id="{{ $project->project_id }}">
              <i class="fi fi-rr-pencil"></i>
            </button>
            @if($project->project_status === 'deleted')
            <button class="action-btn restore-btn p-2 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition" title="Restore" data-id="{{ $project->project_id }}">
              <i class="fi fi-rr-refresh"></i>
            </button>
            @else
            <button class="action-btn delete-btn p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition" title="Delete" data-id="{{ $project->project_id }}">
              <i class="fi fi-rr-trash"></i>
            </button>
            @endif
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" class="px-6 py-12 text-center">
          <div class="flex flex-col items-center justify-center gap-3">
            <i class="fi fi-rr-folder-open text-gray-400 text-5xl"></i>
            <p class="text-gray-500 font-medium">No projects found</p>
            <p class="text-gray-400 text-sm">Try adjusting your search or filters</p>
          </div>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
  {{ $projects->links() }}
</div>
