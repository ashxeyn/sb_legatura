<!-- Showcase Table Partial -->
@forelse ($showcases as $showcase)
    @php
        $contractorInitials = strtoupper(substr($showcase->contractor_name ?? '?', 0, 2));
        $statusClasses = [
            'approved' => 'bg-green-100 text-green-700 border-green-200',
            'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
            'rejected' => 'bg-red-100 text-red-700 border-red-200',
            'open' => 'bg-blue-100 text-blue-700 border-blue-200',
            'closed' => 'bg-gray-100 text-gray-600 border-gray-200',
            'deleted' => 'bg-gray-100 text-gray-600 border-gray-200',
        ];
        $statusLabels = [
            'approved' => 'Approved',
            'pending' => 'Pending Review',
            'rejected' => 'Rejected',
            'open' => 'Open',
            'closed' => 'Closed',
            'deleted' => 'Deleted',
        ];
        $statusClass = $statusClasses[$showcase->status] ?? 'bg-gray-100 text-gray-600 border-gray-200';
        $statusLabel = $statusLabels[$showcase->status] ?? ucfirst($showcase->status);
    @endphp
    <tr class="hover:bg-indigo-50/60 transition-colors showcase-row cursor-pointer" data-id="{{ $showcase->post_id }}" data-status="{{ strtolower((string) $showcase->status) }}">
        <td class="px-2.5 py-2.5">
            <div class="flex items-center gap-1.5">
                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center overflow-hidden shadow flex-shrink-0">
                    @if($showcase->contractor_pic)
                        <img src="{{ asset('storage/' . $showcase->contractor_pic) }}" alt="{{ $showcase->contractor_name }}"
                            class="w-full h-full object-cover">
                    @else
                        <span class="text-white text-[10px] font-bold">{{ $contractorInitials }}</span>
                    @endif
                </div>
                <div class="min-w-0">
                    <div class="font-medium text-gray-800 leading-tight text-xs truncate max-w-[150px]"
                        title="{{ $showcase->contractor_name }}">{{ $showcase->contractor_name }}</div>
                    <div class="text-[11px] text-gray-500">Post #{{ $showcase->post_id }}</div>
                </div>
            </div>
        </td>

        <td class="px-2.5 py-2.5">
            <div class="min-w-0">
                <div class="flex items-center gap-1.5 min-w-0">
                    <span class="font-medium text-gray-800 leading-tight text-xs truncate max-w-[180px]"
                        title="{{ $showcase->title }}">{{ $showcase->title }}</span>
                    @if($showcase->is_highlighted)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-semibold border border-amber-200 bg-amber-100 text-amber-700 uppercase">Featured</span>
                    @endif
                </div>
                <div class="text-[11px] text-gray-500 truncate max-w-[190px]"
                    title="{{ $showcase->location ?: 'No location set' }}">{{ $showcase->location ?: 'No location set' }}</div>
            </div>
        </td>

        <td class="px-2.5 py-2.5 text-gray-700 text-xs max-w-[130px]">
            @if($showcase->linked_project_id)
                <span class="block truncate font-medium text-indigo-700" title="Project #{{ $showcase->linked_project_id }}">
                    Project #{{ $showcase->linked_project_id }}
                </span>
            @else
                <span class="text-gray-400 italic text-[11px]">No linked project</span>
            @endif
        </td>

        <td class="px-2.5 py-2.5 whitespace-nowrap text-gray-700 text-[11px]">
            {{ \Carbon\Carbon::parse($showcase->created_at)->format('F j, Y') }}
        </td>

        <td class="px-2.5 py-2.5 text-center">
            <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $statusClass }}">
                {{ $statusLabel }}
            </span>
        </td>

        <td class="px-2.5 py-2.5 whitespace-nowrap">
            <div class="flex items-center gap-1">
                <button
                    class="view-showcase-btn action-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-indigo-100 hover:shadow-sm hover:border-indigo-300 hover:-translate-y-0.5 transition-all"
                    title="View" data-id="{{ $showcase->post_id }}" data-status="{{ strtolower((string) $showcase->status) }}">
                    <i class="fi fi-rr-eye text-[13px] leading-none"></i>
                </button>

                @if($showcase->status === 'deleted')
                    <button
                        class="restore-showcase-btn action-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-green-200 bg-green-50 text-green-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-green-100 hover:shadow-sm hover:border-green-300 hover:-translate-y-0.5 transition-all"
                        title="Restore" data-id="{{ $showcase->post_id }}" data-title="{{ $showcase->title }}">
                        <svg class="w-[13px] h-[13px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                    </button>
                @else
                    <button
                        class="delete-showcase-btn action-btn w-8 h-8 inline-flex items-center justify-center p-1.5 rounded-xl border border-red-200 bg-red-50 text-red-600 shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:bg-red-100 hover:shadow-sm hover:border-red-300 hover:-translate-y-0.5 transition-all"
                        title="Delete" data-id="{{ $showcase->post_id }}" data-title="{{ $showcase->title }}">
                        <i class="fi fi-rr-trash text-[13px] leading-none"></i>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="px-4 py-12 text-center text-gray-400">
            <i class="fi fi-sr-layers text-3xl block mb-2 text-indigo-200"></i>
            <p class="text-base font-medium text-gray-500">No showcase posts found</p>
            <p class="text-xs mt-1">Try adjusting your search or filter criteria.</p>
        </td>
    </tr>
@endforelse