<div class="overflow-x-auto">
  <table class="w-full">
    <thead>
      <tr class="bg-gray-50 border-b border-gray-200">
        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Date Registered</th>
        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Occupation</th>
        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Number of<br>Project Posted</th>
        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Ongoing<br>Projects</th>
        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200" id="propertyOwnersTable">
      @forelse($propertyOwners as $propertyOwner)
      <tr class="hover:bg-gray-50 transition duration-150 ease-in-out group">
        <td class="px-6 py-4">
          <div class="flex items-center gap-3">
            @if($propertyOwner->profile_pic)
                <img src="{{ asset('storage/' . $propertyOwner->profile_pic) }}" alt="Profile" class="w-10 h-10 rounded-full object-cover shadow-md flex-shrink-0">
            @else
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white font-semibold shadow-md flex-shrink-0">
                {{ substr($propertyOwner->first_name, 0, 1) . substr($propertyOwner->last_name, 0, 1) }}
                </div>
            @endif
            <span class="font-medium text-gray-900">{{ $propertyOwner->first_name }} {{ $propertyOwner->last_name }}</span>
          </div>
        </td>
        <td class="px-6 py-4 text-center">
          <div class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($propertyOwner->created_at)->format('d M, Y') }}</div>
        </td>
        <td class="px-6 py-4 text-center">
          <span class="text-sm text-gray-700">{{ $propertyOwner->occupation ?? 'N/A' }}</span>
        </td>
        <td class="px-6 py-4 text-center">
          <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-semibold text-sm">{{ $propertyOwner->posted_projects_count ?? 0 }}</span>
        </td>
        <td class="px-6 py-4 text-center">
          <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-700 font-semibold text-sm">{{ $propertyOwner->ongoing_projects_count ?? 0 }}</span>
        </td>
        <td class="px-6 py-4">
          <div class="flex items-center justify-center gap-2">
            <button class="action-btn view-btn p-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="View" data-id="{{ $propertyOwner->owner_id }}">
              <i class="fi fi-rr-eye"></i>
            </button>
            <button class="action-btn edit-btn p-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition" title="Edit" data-id="{{ $propertyOwner->owner_id }}">
              <i class="fi fi-rr-pencil"></i>
            </button>
            <button class="action-btn delete-btn p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition" title="Delete" data-id="{{ $propertyOwner->owner_id }}">
              <i class="fi fi-rr-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
            No property owners found.
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="px-6 py-4 border-t border-gray-200">
      {{ $propertyOwners->links() }}
  </div>
</div>
