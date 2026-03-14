<div class="overflow-x-auto">
  <table class="w-full table-fixed">
    <thead>
      <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[28%]">Name</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[14%]">Date Registered</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[20%]">Occupation</th>
        <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[12%]">Projects<br>Posted</th>
        <th class="px-2.5 py-2.5 text-center text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[12%]">Ongoing<br>Projects</th>
        <th class="px-2.5 py-2.5 text-left text-[11px] font-semibold text-gray-700 uppercase tracking-wider w-[14%]">Action</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200" id="propertyOwnersTable">
      @forelse($propertyOwners as $propertyOwner)
      @php
        $ownerInitials = strtoupper(substr($propertyOwner->first_name ?? '?', 0, 1) . substr($propertyOwner->last_name ?? '', 0, 1));
        $ownerPalette  = ['from-blue-500 to-indigo-600','from-violet-500 to-purple-600','from-emerald-500 to-teal-600','from-fuchsia-500 to-purple-600','from-rose-500 to-red-600','from-amber-500 to-orange-600','from-cyan-500 to-sky-600','from-lime-600 to-green-700'];
        $ownerColor    = $ownerPalette[$propertyOwner->owner_id % count($ownerPalette)];
      @endphp
      <tr class="hover:bg-indigo-50/60 transition-colors">
        <td class="px-2.5 py-2.5">
          <div class="flex items-center gap-1.5">
            @if($propertyOwner->profile_pic)
              <img src="{{ asset('storage/' . $propertyOwner->profile_pic) }}" alt="Profile" class="w-7 h-7 rounded-full object-cover shadow flex-shrink-0">
            @else
              <div class="w-7 h-7 rounded-full bg-gradient-to-br {{ $ownerColor }} flex items-center justify-center text-white text-[10px] font-bold shadow flex-shrink-0">{{ $ownerInitials }}</div>
            @endif
            <div class="min-w-0">
              <div class="font-medium text-gray-800 leading-tight text-xs truncate max-w-[160px]" title="{{ $propertyOwner->first_name }} {{ $propertyOwner->last_name }}">{{ $propertyOwner->first_name }} {{ $propertyOwner->last_name }}</div>
            </div>
          </div>
        </td>
        <td class="px-2.5 py-2.5 whitespace-nowrap text-gray-700 text-[11px]">{{ \Carbon\Carbon::parse($propertyOwner->created_at)->format('F j, Y') }}</td>
        <td class="px-2.5 py-2.5 text-gray-700 text-xs max-w-[130px]">
          <span class="block truncate" title="{{ $propertyOwner->occupation ?? 'N/A' }}">{{ $propertyOwner->occupation ?? 'N/A' }}</span>
        </td>
        <td class="px-2.5 py-2.5 text-center">
          <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-indigo-100 text-indigo-700 border-indigo-200">{{ $propertyOwner->posted_projects_count ?? 0 }}</span>
        </td>
        <td class="px-2.5 py-2.5 text-center">
          <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] font-semibold border bg-green-100 text-green-700 border-green-200">{{ $propertyOwner->ongoing_projects_count ?? 0 }}</span>
        </td>
        <td class="px-2.5 py-2.5 whitespace-nowrap">
          <div class="flex items-center gap-1">
            <button class="compact-action-btn action-btn view-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-indigo-100 hover:shadow-sm hover:border-indigo-300 hover:-translate-y-0.5 transition-all active:scale-95" title="View" data-id="{{ $propertyOwner->owner_id }}">
              <i class="fi fi-rr-eye text-[13px] leading-none"></i>
            </button>
            <button class="compact-action-btn action-btn edit-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-orange-200 bg-orange-50 text-orange-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-orange-100 hover:shadow-sm hover:border-orange-300 hover:-translate-y-0.5 transition-all active:scale-95" title="Edit" data-id="{{ $propertyOwner->owner_id }}">
              <i class="fi fi-rr-pencil text-[13px] leading-none"></i>
            </button>
            <button class="compact-action-btn action-btn delete-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-red-200 bg-red-50 text-red-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-red-100 hover:shadow-sm hover:border-red-300 hover:-translate-y-0.5 transition-all active:scale-95" title="Delete" data-id="{{ $propertyOwner->owner_id }}">
              <i class="fi fi-rr-trash text-[13px] leading-none"></i>
            </button>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="6" class="px-4 py-12 text-center text-gray-400">
          <i class="fi fi-sr-users text-3xl block mb-2"></i>
          <p class="text-base font-medium text-gray-500">No property owners found</p>
          <p class="text-xs mt-1">Try adjusting your search or filter criteria.</p>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>

  @if($propertyOwners->hasPages())
  <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-2">
    <p class="text-xs text-gray-500">
      Showing <strong>{{ $propertyOwners->firstItem() }}</strong>–<strong>{{ $propertyOwners->lastItem() }}</strong>
      of <strong>{{ $propertyOwners->total() }}</strong> results
    </p>
    <div class="flex items-center gap-1">
      @if($propertyOwners->onFirstPage())
        <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">‹ Prev</span>
      @else
        <a href="{{ $propertyOwners->previousPageUrl() }}" class="owner-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">‹ Prev</a>
      @endif

      @foreach($propertyOwners->getUrlRange(max(1, $propertyOwners->currentPage()-2), min($propertyOwners->lastPage(), $propertyOwners->currentPage()+2)) as $page => $url)
        @if($page == $propertyOwners->currentPage())
          <span class="px-2.5 py-1 rounded-lg text-xs bg-indigo-600 text-white font-semibold">{{ $page }}</span>
        @else
          <a href="{{ $url }}" class="owner-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">{{ $page }}</a>
        @endif
      @endforeach

      @if($propertyOwners->hasMorePages())
        <a href="{{ $propertyOwners->nextPageUrl() }}" class="owner-page-link px-2.5 py-1 rounded-lg text-xs border border-gray-200 hover:bg-gray-50 transition">Next ›</a>
      @else
        <span class="px-2.5 py-1 rounded-lg text-xs text-gray-400 border border-gray-200 cursor-not-allowed">Next ›</span>
      @endif
    </div>
  </div>
  @else
  <div class="px-4 py-3 border-t border-gray-200">
    <p class="text-xs text-gray-500">
      Showing <strong>{{ $propertyOwners->total() }}</strong> result(s)
    </p>
  </div>
  @endif
</div>
