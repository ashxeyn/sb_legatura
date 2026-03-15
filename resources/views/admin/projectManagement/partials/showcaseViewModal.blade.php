<!-- Showcase View Modal Content (Server-side rendered partial) -->
<!-- This partial receives $showcase (from getShowcaseDetails) -->

@php
    $rawStatus = strtolower((string) ($showcase['post']['status'] ?? 'pending'));
    if ($rawStatus === 'pending_review' || $rawStatus === 'under_review') {
        $st = 'pending';
    } elseif (in_array($rawStatus, ['approved', 'rejected', 'pending'], true)) {
        $st = $rawStatus;
    } else {
        $st = 'pending';
    }

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

    $themeByStatus = [
        'approved' => [
            'heroCard' => 'border-green-100 bg-gradient-to-r from-green-50 to-emerald-50',
            'avatar' => 'from-green-500 to-emerald-500',
            'label' => 'text-green-700',
            'galleryHeader' => 'from-green-50 to-emerald-50',
            'galleryIcon' => 'text-green-600',
            'galleryCount' => 'border-green-200 text-green-700',
            'galleryHover' => 'hover:border-green-400',
        ],
        'pending' => [
            'heroCard' => 'border-orange-100 bg-gradient-to-r from-orange-50 to-amber-50',
            'avatar' => 'from-orange-400 to-amber-500',
            'label' => 'text-orange-700',
            'galleryHeader' => 'from-orange-50 to-amber-50',
            'galleryIcon' => 'text-orange-600',
            'galleryCount' => 'border-orange-200 text-orange-700',
            'galleryHover' => 'hover:border-orange-400',
        ],
        'rejected' => [
            'heroCard' => 'border-red-100 bg-gradient-to-r from-red-50 to-rose-50',
            'avatar' => 'from-red-500 to-rose-500',
            'label' => 'text-red-700',
            'galleryHeader' => 'from-red-50 to-rose-50',
            'galleryIcon' => 'text-red-600',
            'galleryCount' => 'border-red-200 text-red-700',
            'galleryHover' => 'hover:border-red-400',
        ],
    ];

    $theme = $themeByStatus[$st] ?? $themeByStatus['pending'];
    $contractorName = $showcase['contractor']['name'] ?? 'Unknown Contractor';
    $showcaseTitle = $showcase['post']['title'] ?? 'Untitled Showcase';
    $showcaseContent = $showcase['post']['content'] ?? 'No description provided.';
    $location = $showcase['post']['location'] ?? 'Not specified';
    $linkedProject = $showcase['linked_project_title'] ?? 'No linked project';
    $imageCount = count($showcase['images']);
    $isHighlighted = !empty($showcase['post']['is_highlighted']);

    $postedAt = !empty($showcase['post']['created_at'])
        ? \Carbon\Carbon::parse($showcase['post']['created_at'])->format('F j, Y')
        : 'Unknown date';

    $initials = '';
    foreach (preg_split('/\s+/', trim($contractorName)) as $part) {
        if ($part !== '') {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        if (strlen($initials) >= 2) {
            break;
        }
    }
    $initials = $initials !== '' ? $initials : 'NA';
@endphp

<div class="space-y-3.5">
    <!-- Contractor + status -->
    <div class="rounded-xl border p-3.5 {{ $theme['heroCard'] }}">
        <div class="flex items-start gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br rounded-full flex items-center justify-center text-white font-bold text-sm shadow-md overflow-hidden flex-shrink-0 {{ $theme['avatar'] }}">
                @if(!empty($showcase['contractor']['profile_pic']))
                    <img src="{{ asset('storage/' . $showcase['contractor']['profile_pic']) }}" alt="Profile"
                        class="w-full h-full object-cover">
                @else
                    {{ $initials }}
                @endif
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wide {{ $theme['label'] }}">Contractor</p>
                <h4 class="text-base font-bold text-gray-900 truncate">{{ $contractorName }}</h4>
                <p class="text-xs text-gray-600">Posted on {{ $postedAt }}</p>
            </div>
            <span
                class="ml-auto px-2.5 py-1 rounded-full text-[11px] font-bold border whitespace-nowrap {{ $statusClasses[$st] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                {{ $statusLabels[$st] ?? ucfirst($st) }}
            </span>
        </div>
    </div>

    <!-- Showcase summary -->
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-3.5">
        <div class="flex items-start justify-between gap-3 mb-2">
            <h5 class="text-base font-bold text-gray-900 leading-tight">{{ $showcaseTitle }}</h5>
            @if($isHighlighted)
                <span class="px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wide bg-yellow-100 text-yellow-700 border border-yellow-200">
                    Featured
                </span>
            @endif
        </div>
        <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $showcaseContent }}</p>
    </div>

    <!-- Details -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5">
            <p class="text-[11px] uppercase tracking-wide text-gray-500 font-semibold mb-1">Location</p>
            <p class="text-sm font-semibold text-gray-800 break-words">{{ $location }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5">
            <p class="text-[11px] uppercase tracking-wide text-gray-500 font-semibold mb-1">Linked Project</p>
            <p class="text-sm font-semibold text-gray-800 break-words">{{ $linkedProject }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5">
            <p class="text-[11px] uppercase tracking-wide text-gray-500 font-semibold mb-1">Highlight</p>
            <p class="text-sm font-semibold text-gray-800">{{ $isHighlighted ? 'Yes, featured post' : 'No' }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5">
            <p class="text-[11px] uppercase tracking-wide text-gray-500 font-semibold mb-1">Images</p>
            <p class="text-sm font-semibold text-gray-800">{{ $imageCount }} file(s)</p>
        </div>
    </div>

    <!-- Rejection Reason -->
    @if($st === 'rejected' && !empty($showcase['post']['rejection_reason']))
        <div class="rounded-xl border border-red-200 bg-red-50 px-3.5 py-3">
            <p class="text-sm font-semibold text-red-700 mb-1">Rejection Reason</p>
            <p class="text-sm text-red-600 leading-relaxed">{{ $showcase['post']['rejection_reason'] }}</p>
        </div>
    @endif

    <!-- Images -->
    <div class="rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r px-4 py-2.5 border-b border-gray-200 flex items-center justify-between {{ $theme['galleryHeader'] }}">
            <h5 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-4 h-4 {{ $theme['galleryIcon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Showcase Images
            </h5>
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-white border {{ $theme['galleryCount'] }}">
                {{ $imageCount }}
            </span>
        </div>
        <div class="p-3.5">
            @if($imageCount > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    @foreach($showcase['images'] as $image)
                        <a href="#"
                            class="group rounded-lg overflow-hidden border border-gray-200 aspect-video bg-gray-100 block transition-colors cursor-pointer open-doc-btn {{ $theme['galleryHover'] }}"
                            data-doc-src="{{ asset('storage/' . $image->file_path) }}"
                            data-doc-title="{{ $image->original_name ?? 'Showcase image' }}">
                            <img src="{{ asset('storage/' . $image->file_path) }}"
                                alt="{{ $image->original_name ?? 'Showcase image' }}"
                                class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-200">
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 py-6 text-center">
                    <p class="text-sm text-gray-500 font-medium">No images attached</p>
                </div>
            @endif
        </div>
    </div>
</div>