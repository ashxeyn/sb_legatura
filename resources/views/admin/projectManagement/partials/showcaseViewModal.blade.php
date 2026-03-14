<!-- Showcase View Modal Content (Server-side rendered partial) -->
<!-- This partial receives $showcase (from getShowcaseDetails) -->

<!-- Contractor Info -->
<div class="flex items-center gap-4 pb-4 border-b">
    <div
        class="w-14 h-14 bg-gradient-to-br from-orange-400 to-amber-500 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg overflow-hidden">
        @if($showcase['contractor']['profile_pic'])
            <img src="{{ asset('storage/' . $showcase['contractor']['profile_pic']) }}" alt="Profile"
                class="w-full h-full object-cover">
        @else
            {{ strtoupper(substr($showcase['contractor']['name'], 0, 2)) }}
        @endif
    </div>
    <div>
        <h4 class="text-lg font-bold text-gray-800">{{ $showcase['contractor']['name'] }}</h4>
        <p class="text-sm text-gray-500">
            {{ \Carbon\Carbon::parse($showcase['post']['created_at'])->format('F j, Y') }}</p>
    </div>
    <div class="ml-auto">
        @php
            $statusClasses = [
                'approved' => 'bg-green-100 text-green-700 border-green-200',
                'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                'rejected' => 'bg-red-100 text-red-700 border-red-200',
            ];
            $statusLabels = [
                'approved' => 'Approved',
                'pending' => 'Pending Review',
                'rejected' => 'Rejected',
            ];
            $st = $showcase['post']['status'];
        @endphp
        <span
            class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusClasses[$st] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
            {{ $statusLabels[$st] ?? ucfirst($st) }}
        </span>
    </div>
</div>

<!-- Showcase Content -->
<div class="bg-gray-50 rounded-xl p-5">
    <h5 class="text-lg font-bold text-gray-800 mb-2">{{ $showcase['post']['title'] ?? '—' }}</h5>
    <p class="text-sm text-gray-700 leading-relaxed">{{ $showcase['post']['content'] ?? '—' }}</p>
</div>

<!-- Details Grid -->
<div class="grid grid-cols-2 gap-4">
    <div>
        <p class="text-sm text-gray-600 mb-1">Location</p>
        <p class="font-semibold text-gray-800">{{ $showcase['post']['location'] ?? '—' }}</p>
    </div>
    <div>
        <p class="text-sm text-gray-600 mb-1">Linked Project</p>
        <p class="font-semibold text-gray-800">{{ $showcase['linked_project_title'] ?? 'No linked project' }}</p>
    </div>
    <div>
        <p class="text-sm text-gray-600 mb-1">Highlighted</p>
        <p class="font-semibold text-gray-800">{{ $showcase['post']['is_highlighted'] ? 'Yes — Featured' : 'No' }}</p>
    </div>
    <div>
        <p class="text-sm text-gray-600 mb-1">Images</p>
        <p class="font-semibold text-gray-800">{{ count($showcase['images']) }} image(s)</p>
    </div>
</div>

<!-- Rejection Reason (only show if rejected) -->
@if($showcase['post']['status'] === 'rejected' && !empty($showcase['post']['rejection_reason']))
    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
        <p class="text-sm font-semibold text-red-700 mb-1">Rejection Reason</p>
        <p class="text-sm text-red-600">{{ $showcase['post']['rejection_reason'] }}</p>
    </div>
@endif

<!-- Image Gallery -->
<div class="rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="bg-gradient-to-r from-orange-50 to-amber-50 px-5 py-3 border-b border-gray-200">
        <h5 class="font-semibold text-gray-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Showcase Images
        </h5>
    </div>
    <div class="px-5 py-4">
        @if(count($showcase['images']) > 0)
            <div class="grid grid-cols-3 gap-3">
                @foreach($showcase['images'] as $image)
                    <div class="rounded-lg overflow-hidden border border-gray-200 aspect-video bg-gray-100">
                        <img src="{{ asset('storage/' . $image->file_path) }}"
                            alt="{{ $image->original_name ?? 'Showcase image' }}" class="w-full h-full object-cover">
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400 text-center py-4">No images attached</p>
        @endif
    </div>
</div>