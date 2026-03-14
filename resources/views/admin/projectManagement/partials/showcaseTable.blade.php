<!-- Showcase Table Partial -->
@forelse ($showcases as $showcase)
    <tr class="hover:bg-orange-50/40 transition-colors duration-150 cursor-pointer showcase-row"
        data-id="{{ $showcase->post_id }}">
        <!-- Contractor -->
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center gap-3">
                <div
                    class="w-9 h-9 bg-gradient-to-br from-orange-400 to-amber-500 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-sm flex-shrink-0 overflow-hidden">
                    @if($showcase->contractor_pic)
                        <img src="{{ asset('storage/' . $showcase->contractor_pic) }}" alt="Profile"
                            class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($showcase->contractor_name, 0, 1)) }}
                    @endif
                </div>
                <span class="text-sm font-semibold text-gray-800">{{ $showcase->contractor_name }}</span>
            </div>
        </td>

        <!-- Showcase Title -->
        <td class="px-6 py-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-800">{{ $showcase->title }}</span>
                @if($showcase->is_highlighted)
                    <span
                        class="px-1.5 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-md uppercase">Featured</span>
                @endif
            </div>
        </td>

        <!-- Linked Project -->
        <td class="px-6 py-4">
            @if($showcase->linked_project_id)
                <span class="text-sm text-blue-600 font-medium">Project #{{ $showcase->linked_project_id }}</span>
            @else
                <span class="text-sm text-gray-400 italic">No linked project</span>
            @endif
        </td>

        <!-- Date Posted -->
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($showcase->created_at)->format('F j, Y') }}</span>
        </td>

        <!-- Status -->
        <td class="px-6 py-4 whitespace-nowrap">
            @php
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
                $cls = $statusClasses[$showcase->status] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                $lbl = $statusLabels[$showcase->status] ?? ucfirst($showcase->status);
              @endphp
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold border {{ $cls }}">
                <span
                    class="w-1.5 h-1.5 rounded-full {{ str_contains($cls, 'green') ? 'bg-green-500' : (str_contains($cls, 'amber') ? 'bg-amber-500' : (str_contains($cls, 'red') ? 'bg-red-500' : 'bg-gray-400')) }}"></span>
                {{ $lbl }}
            </span>
        </td>

        <!-- Action -->
        <td class="px-6 py-4 whitespace-nowrap text-center">
            <div class="flex items-center justify-center gap-2">
                <button
                    class="view-showcase-btn p-1.5 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors duration-200 border border-blue-200"
                    title="View Details" data-id="{{ $showcase->post_id }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>

                @if($showcase->status === 'deleted')
                    <button
                        class="restore-showcase-btn p-1.5 text-green-600 bg-green-50 hover:bg-green-100 rounded-lg transition-colors duration-200 border border-green-200"
                        title="Restore Showcase" data-id="{{ $showcase->post_id }}" data-title="{{ $showcase->title }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                    </button>
                @else
                    <button
                        class="delete-showcase-btn p-1.5 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors duration-200 border border-red-200"
                        title="Delete Showcase" data-id="{{ $showcase->post_id }}" data-title="{{ $showcase->title }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="px-6 py-12 text-center">
            <div class="flex flex-col items-center gap-2">
                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <p class="text-sm text-gray-400 font-medium">No showcase posts found</p>
            </div>
        </td>
    </tr>
@endforelse