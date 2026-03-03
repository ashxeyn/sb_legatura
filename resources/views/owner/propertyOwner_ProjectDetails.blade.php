@extends('layouts.app')

@section('title', 'Project Details - Legatura')

@section('content')
    <div class="project-details-page min-h-screen bg-gray-50">
        <!-- Header with Back Button -->
        <div class="project-details-header">
            <div class="container">
                <button class="back-btn" onclick="window.location.href='/owner/profile'">
                    <i class="fi fi-rr-arrow-left"></i>
                    <span>Back to Profile</span>
                </button>
                <h1 class="page-title">{{ $project->project_title ?? 'Project Details' }}</h1>
            </div>
        </div>

        <!-- Two-Column Layout: Files & Information -->
        <div class="project-two-column-layout">

            <!-- Left Column: Project Files -->
            <div class="project-files-column">
                <!-- First Row: Design Files (Larger) -->
                <div class="project-files-section">
                    <div class="project-files-header">
                        <i class="fi fi-rr-blueprint"></i>
                        <span class="project-files-title">Design Files</span>
                    </div>
                    <div class="project-files-grid project-files-grid-large" id="projectDesignFiles">
                        <div class="project-files-empty">
                            <i class="fi fi-rr-image"></i>
                            <span>No design files uploaded</span>
                        </div>
                    </div>
                </div>

                <!-- Second Row: Legal Documents (Smaller) -->
                <div class="project-files-section">
                    <div class="project-files-header">
                        <i class="fi fi-rr-document-signed"></i>
                        <span class="project-files-title">Legal Documents</span>
                    </div>
                    <div class="project-files-grid project-files-grid-small" id="projectLegalFiles">
                        <div class="project-files-empty">
                            <i class="fi fi-rr-file"></i>
                            <span>No legal documents uploaded</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Project Information -->
            <div class="project-info-column">
                <div class="project-info-card-main">
                    <div class="project-info-header">
                        <i class="fi fi-rr-info"></i>
                        <span>Project Information</span>
                    </div>

                    <div class="project-info-body">
                        <!-- Status -->
                        <div class="project-info-row">
                            <span class="project-info-label">Status</span>
                            <span class="project-info-value">
                                @php
                                    $status = $project->project_status ?? 'open';
                                @endphp
                                @if($status === 'completed')
                                    <span class="status-badge status-completed">Completed</span>
                                @elseif($status === 'in_progress')
                                    <span class="status-badge status-ongoing">In Progress</span>
                                @elseif($status === 'open')
                                    <span class="status-badge status-pending">Open</span>
                                @elseif($status === 'bidding_closed')
                                    <span class="status-badge status-pending">Bidding Closed</span>
                                @else
                                    <span class="status-badge">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                                @endif
                            </span>
                        </div>

                        <!-- Posted -->
                        <div class="project-info-row">
                            <span class="project-info-label">Posted</span>
                            <span class="project-info-value">{{ \Carbon\Carbon::parse($project->created_at)->format('M d, Y') }}</span>
                        </div>

                        <!-- Deadline -->
                        @if(isset($project->bidding_due) && $project->bidding_due)
                        <div class="project-info-row">
                            <span class="project-info-label">Bidding Deadline</span>
                            <span class="project-info-value">{{ \Carbon\Carbon::parse($project->bidding_due)->format('M d, Y') }}</span>
                        </div>
                        @endif

                        <!-- Bids -->
                        <div class="project-info-row">
                            <span class="project-info-label">Bids</span>
                            <span class="project-info-value">{{ $bidsCount }} {{ $bidsCount === 1 ? 'bid' : 'bids' }}</span>
                        </div>

                        <!-- Budget Range -->
                        <div class="project-info-row">
                            <span class="project-info-label">Budget Range</span>
                            <span class="project-info-value">₱{{ number_format($project->budget_range_min ?? 0) }} - ₱{{ number_format($project->budget_range_max ?? 0) }}</span>
                        </div>

                        <!-- Location -->
                        @if($project->project_location)
                        <div class="project-info-row">
                            <span class="project-info-label">Location</span>
                            <span class="project-info-value">{{ $project->project_location }}</span>
                        </div>
                        @endif

                        <!-- Contractor Type -->
                        @if(isset($project->type_name))
                        <div class="project-info-row">
                            <span class="project-info-label">Contractor Type</span>
                            <span class="project-info-value">{{ $project->type_name }}</span>
                        </div>
                        @endif

                        <!-- Description Section -->
                        @if($project->project_description)
                        <div class="project-info-section">
                            <span class="project-info-section-title">Description</span>
                            <p class="project-info-description">{{ $project->project_description }}</p>
                        </div>
                        @endif

                        <!-- Specifications Section -->
                        <div class="project-info-section">
                            <span class="project-info-section-title">Specifications</span>
                            <div class="project-info-specs">
                                @if($project->property_type)
                                <div class="project-info-spec-item">
                                    <span class="project-info-spec-label">Property Type</span>
                                    <span class="project-info-spec-value">{{ $project->property_type }}</span>
                                </div>
                                @endif

                                @if(isset($project->lot_size) && $project->lot_size)
                                <div class="project-info-spec-item">
                                    <span class="project-info-spec-label">Lot Size</span>
                                    <span class="project-info-spec-value">{{ $project->lot_size }} sq.m</span>
                                </div>
                                @endif

                                @if(isset($project->floor_area) && $project->floor_area)
                                <div class="project-info-spec-item">
                                    <span class="project-info-spec-label">Floor Area</span>
                                    <span class="project-info-spec-value">{{ $project->floor_area }} sq.m</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Image Lightbox Modal --}}
    <div id="projectImageLightbox" class="project-lightbox" style="display:none;">
        <div class="project-lightbox-overlay"></div>
        <div class="project-lightbox-content">
            <button class="project-lightbox-close" id="lightboxClose"><i class="fi fi-rr-cross"></i></button>
            <button class="project-lightbox-nav project-lightbox-prev" id="lightboxPrev"><i class="fi fi-rr-angle-left"></i></button>
            <button class="project-lightbox-nav project-lightbox-next" id="lightboxNext"><i class="fi fi-rr-angle-right"></i></button>
            <div class="project-lightbox-img-wrapper">
                <img id="lightboxImg" src="" alt="Preview">
                <div class="project-lightbox-watermark" id="lightboxWatermark"></div>
                <div class="project-lightbox-protected-label" id="lightboxProtectedLabel">
                    <i class="fi fi-rr-shield-check"></i> Protected Document
                </div>
            </div>
            <div class="project-lightbox-counter" id="lightboxCounter"></div>
        </div>
    </div>

    <style>
        /* ── Page Container ──────────────────────────────────────────────── */
        .project-details-page {
            padding-bottom: 40px;
        }

        /* ── Header ──────────────────────────────────────────────────────── */
        .project-details-header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 20px 0;
            margin-bottom: 20px;
        }

        .project-details-header .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 80px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: white;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 16px;
        }

        .back-btn:hover {
            border-color: #EC7E00;
            color: #EC7E00;
            background: #FFF3E6;
        }

        .back-btn i {
            font-size: 16px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
        }

        /* ── Two-Column Layout ───────────────────────────────────────────── */
        .project-two-column-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 80px;
            width: 100%;
        }

        /* ── Left Column: Files ──────────────────────────────────────────── */
        .project-files-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .project-files-section {
            background: white;
            border-radius: 12px;
            border: 1.5px solid #e5e7eb;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .project-files-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 20px;
            background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);
            border-bottom: 1.5px solid #e5e7eb;
        }

        .project-files-header i {
            font-size: 18px;
            color: #EC7E00;
        }

        .project-files-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
        }

        /* ── File Grids ────────────────────────────────────────────────── */
        .project-files-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 14px;
            padding: 20px;
            min-height: 140px;
        }

        .project-files-grid-large {
            min-height: 200px;
        }

        .project-files-grid-small {
            min-height: 130px;
        }

        /* ── File Preview Card ────────────────────────────────────────────── */
        .project-file-preview {
            position: relative;
            background: #F8FAFC;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            aspect-ratio: 4/3;
        }

        .project-file-preview:hover {
            border-color: #EC7E00;
            box-shadow: 0 6px 16px rgba(236, 126, 0, 0.2);
            transform: translateY(-3px);
        }

        .project-file-preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .project-file-preview-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            gap: 10px;
            padding: 14px;
        }

        .project-file-preview-icon i {
            font-size: 36px;
            color: #64748b;
        }

        .project-file-name {
            font-size: 11px;
            color: #94a3b8;
            text-align: center;
            word-break: break-word;
            line-height: 1.4;
            max-width: 100%;
        }

        .project-file-type-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 5px 10px;
            border-radius: 6px;
            text-transform: uppercase;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        }

        /* ── Empty State ──────────────────────────────────────────────────── */
        .project-files-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            height: 100%;
            color: #94a3b8;
            grid-column: 1 / -1;
        }

        .project-files-empty i {
            font-size: 40px;
            opacity: 0.4;
        }

        .project-files-empty span {
            font-size: 13px;
            font-weight: 500;
        }

        /* ── Right Column: Information ───────────────────────────────────── */
        .project-info-column {
            display: flex;
            flex-direction: column;
        }

        .project-info-card-main {
            background: white;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            height: fit-content;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .project-info-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 20px;
            background: linear-gradient(135deg, #FFF3E6 0%, #FFECD1 100%);
            border-bottom: 1.5px solid #e5e7eb;
        }

        .project-info-header i {
            font-size: 18px;
            color: #EC7E00;
        }

        .project-info-header span {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
        }

        .project-info-body {
            padding: 20px;
        }

        /* ── Info Rows ──────────────────────────────────────────────────── */
        .project-info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .project-info-row:last-of-type {
            border-bottom: none;
        }

        .project-info-label {
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
        }

        .project-info-value {
            font-size: 14px;
            color: #0f172a;
            font-weight: 500;
            text-align: right;
            max-width: 60%;
            word-break: break-word;
        }

        /* ── Status Badges ───────────────────────────────────────────────── */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-ongoing {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        /* ── Info Sections ──────────────────────────────────────────────── */
        .project-info-section {
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1.5px solid #e5e7eb;
        }

        .project-info-section:first-child {
            margin-top: 0;
            padding-top: 0;
            border-top: none;
        }

        .project-info-section-title {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
        }

        .project-info-description {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin: 0;
        }

        .project-info-specs {
            display: grid;
            gap: 10px;
        }

        .project-info-spec-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 14px;
            background: #F8FAFC;
            border-radius: 8px;
        }

        .project-info-spec-label {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }

        .project-info-spec-value {
            font-size: 13px;
            color: #0f172a;
            font-weight: 600;
        }

        /* ── Watermark for Legal Documents ────────────────────────────── */
        .project-file-preview.protected {
            position: relative;
        }

        .project-file-preview.protected::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('{{ asset('img/legatura_watermark.png') }}');
            background-size: 60%;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.3;
            pointer-events: none;
            z-index: 1;
        }

        .project-file-preview.protected .document-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.75));
            color: #fff;
            padding: 1.5rem 0.5rem 0.5rem;
            font-size: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            z-index: 2;
            font-weight: 600;
            text-transform: uppercase;
        }

        .project-file-preview.protected .document-label i {
            font-size: 0.7rem;
        }

        /* ── Image Lightbox ──────────────────────────────────────────────── */
        .project-lightbox {
            position: fixed;
            inset: 0;
            z-index: 10001;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .project-lightbox-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.92);
            backdrop-filter: blur(8px);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .project-lightbox.lb-active .project-lightbox-overlay {
            opacity: 1;
        }

        .project-lightbox-content {
            position: relative;
            z-index: 1;
            max-width: 90vw;
            max-height: 85vh;
            opacity: 0;
            transform: scale(0.92);
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
        }

        .project-lightbox.lb-active .project-lightbox-content {
            opacity: 1;
            transform: scale(1);
        }

        .project-lightbox-img-wrapper {
            position: relative;
            display: inline-block;
            max-width: 90vw;
            max-height: 85vh;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
        }

        .project-lightbox-img-wrapper img {
            display: block;
            max-width: 90vw;
            max-height: 85vh;
            object-fit: contain;
        }

        .project-lightbox-watermark {
            position: absolute;
            inset: 0;
            background-image: url('{{ asset('img/legatura_watermark.png') }}');
            background-repeat: repeat;
            background-size: 180px;
            opacity: 0.35;
            pointer-events: none;
            z-index: 2;
            display: none;
        }

        .project-lightbox-protected-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.75));
            color: #fff;
            padding: 2rem 1rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: none;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            z-index: 3;
            pointer-events: none;
        }

        .project-lightbox-protected-label i {
            font-size: 0.85rem;
        }

        .project-lightbox-close {
            position: absolute;
            top: -12px;
            right: -12px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #fff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1E3A5F;
            font-size: 0.8125rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: all 0.2s;
        }

        .project-lightbox-close:hover {
            background: #FEE2E2;
            color: #EF4444;
            transform: rotate(90deg) scale(1.1);
        }

        .project-lightbox-counter {
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.8125rem;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 600;
            white-space: nowrap;
        }

        .project-lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            z-index: 2;
        }

        .project-lightbox-nav:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-50%) scale(1.1);
        }

        .project-lightbox-prev {
            left: 16px;
        }

        .project-lightbox-next {
            right: 16px;
        }

        /* ── Responsive: Stack on mobile ────────────────────────────────── */
        @media (max-width: 768px) {
            .project-details-header .container {
                padding: 0 16px;
            }

            .page-title {
                font-size: 22px;
            }

            .project-two-column-layout {
                grid-template-columns: 1fr;
                padding: 0 16px;
            }

            .project-files-grid {
                grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
                gap: 12px;
                padding: 14px;
            }
        }
    </style>

    <script>
        // Lightbox state
        let lightboxImages = [];
        let lightboxIndex = 0;

        // Preload watermark image for canvas compositing
        const watermarkImg = new Image();
        watermarkImg.crossOrigin = 'anonymous';
        watermarkImg.src = '{{ asset('img/legatura_watermark.png') }}';

        document.addEventListener('DOMContentLoaded', function() {
            // Project files from Laravel
            const files = @json($files);

            // Populate files on page load
            populateProjectFiles(files);

            // Setup lightbox controls
            setupLightbox();
        });

        function setupLightbox() {
            const lightbox = document.getElementById('projectImageLightbox');
            const closeBtn = document.getElementById('lightboxClose');
            const prevBtn = document.getElementById('lightboxPrev');
            const nextBtn = document.getElementById('lightboxNext');
            const overlay = lightbox?.querySelector('.project-lightbox-overlay');

            if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
            if (prevBtn) prevBtn.addEventListener('click', () => navigateLightbox(-1));
            if (nextBtn) nextBtn.addEventListener('click', () => navigateLightbox(1));
            if (overlay) overlay.addEventListener('click', closeLightbox);

            // Keyboard navigation
            document.addEventListener('keydown', (e) => {
                if (lightbox && lightbox.style.display !== 'none') {
                    if (e.key === 'Escape') closeLightbox();
                    else if (e.key === 'ArrowLeft') navigateLightbox(-1);
                    else if (e.key === 'ArrowRight') navigateLightbox(1);
                }
            });
        }

        function openLightbox(url, index = 0) {
            const lightbox = document.getElementById('projectImageLightbox');
            if (!lightbox) return;

            lightboxIndex = index;
            updateLightbox();
            lightbox.style.display = '';
            requestAnimationFrame(() => lightbox.classList.add('lb-active'));
        }

        function closeLightbox() {
            const lightbox = document.getElementById('projectImageLightbox');
            if (!lightbox) return;

            lightbox.classList.remove('lb-active');
            setTimeout(() => {
                lightbox.style.display = 'none';
                const img = document.getElementById('lightboxImg');
                if (img) img.src = '';
            }, 300);
        }

        function navigateLightbox(dir) {
            if (lightboxImages.length <= 1) return;
            lightboxIndex = (lightboxIndex + dir + lightboxImages.length) % lightboxImages.length;
            updateLightbox();
        }

        function updateLightbox() {
            const img = document.getElementById('lightboxImg');
            const counter = document.getElementById('lightboxCounter');
            const prevBtn = document.getElementById('lightboxPrev');
            const nextBtn = document.getElementById('lightboxNext');
            const watermark = document.getElementById('lightboxWatermark');
            const protectedLabel = document.getElementById('lightboxProtectedLabel');

            const current = lightboxImages[lightboxIndex];

            // Show/hide CSS watermark overlay (visible while canvas renders)
            const showProtected = current && current.isProtected;
            if (watermark) watermark.style.display = showProtected ? 'block' : 'none';
            if (protectedLabel) protectedLabel.style.display = showProtected ? 'flex' : 'none';

            if (img && current) {
                if (current.isProtected) {
                    // Bake watermark into image via canvas so Save-As includes it
                    const srcImg = new Image();
                    srcImg.crossOrigin = 'anonymous';
                    srcImg.onload = function() {
                        const canvas = document.createElement('canvas');
                        canvas.width = srcImg.naturalWidth;
                        canvas.height = srcImg.naturalHeight;
                        const ctx = canvas.getContext('2d');

                        // Draw original image
                        ctx.drawImage(srcImg, 0, 0);

                        // Draw tiled watermark
                        if (watermarkImg.complete && watermarkImg.naturalWidth > 0) {
                            ctx.globalAlpha = 0.30;
                            const tileW = Math.max(180, canvas.width * 0.15);
                            const tileH = tileW * (watermarkImg.naturalHeight / watermarkImg.naturalWidth);
                            for (let y = 0; y < canvas.height; y += tileH + 20) {
                                for (let x = 0; x < canvas.width; x += tileW + 20) {
                                    ctx.drawImage(watermarkImg, x, y, tileW, tileH);
                                }
                            }
                            ctx.globalAlpha = 1.0;
                        }

                        // Draw "Protected Document" gradient label at bottom
                        const labelH = Math.max(40, canvas.height * 0.06);
                        const grad = ctx.createLinearGradient(0, canvas.height - labelH * 2, 0, canvas.height);
                        grad.addColorStop(0, 'rgba(0,0,0,0)');
                        grad.addColorStop(1, 'rgba(0,0,0,0.75)');
                        ctx.fillStyle = grad;
                        ctx.fillRect(0, canvas.height - labelH * 2, canvas.width, labelH * 2);

                        ctx.fillStyle = '#ffffff';
                        ctx.font = `bold ${Math.max(14, canvas.width * 0.018)}px sans-serif`;
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText('PROTECTED DOCUMENT', canvas.width / 2, canvas.height - labelH * 0.6);

                        // Set the baked image as src (right-click save will include watermark)
                        img.src = canvas.toDataURL('image/png');

                        // Hide CSS overlay since watermark is now baked in
                        if (watermark) watermark.style.display = 'none';
                        if (protectedLabel) protectedLabel.style.display = 'none';
                    };
                    srcImg.onerror = function() {
                        // Fallback: show original with CSS overlay
                        img.src = current.url;
                    };
                    // Show original while canvas processes
                    img.src = current.url;
                    srcImg.src = current.url;
                } else {
                    img.src = current.url;
                }
            }

            if (counter) {
                counter.textContent = `${lightboxIndex + 1} / ${lightboxImages.length}`;
                counter.style.display = lightboxImages.length > 1 ? '' : 'none';
            }
            if (prevBtn) prevBtn.style.display = lightboxImages.length > 1 ? '' : 'none';
            if (nextBtn) nextBtn.style.display = lightboxImages.length > 1 ? '' : 'none';
        }

        function populateProjectFiles(files) {
            // Separate files into design and legal categories
            const designFiles = [];
            const legalFiles = [];

            files.forEach(file => {
                const fileType = (file.file_type || '').toLowerCase();
                if (fileType === 'building permit' || fileType === 'title') {
                    legalFiles.push(file);
                } else {
                    // blueprint, desired design, others
                    designFiles.push(file);
                }
            });

            // Render design files grid
            renderFileGrid('projectDesignFiles', designFiles, {
                emptyIcon: 'fi-rr-image',
                emptyText: 'No design files uploaded'
            }, false);

            // Render legal files grid (with watermark)
            renderFileGrid('projectLegalFiles', legalFiles, {
                emptyIcon: 'fi-rr-file',
                emptyText: 'No legal documents uploaded'
            }, true);
        }

        function renderFileGrid(gridId, files, emptyConfig, isProtected = false) {
            const grid = document.getElementById(gridId);
            if (!grid) return;

            if(files.length === 0) {
                grid.innerHTML = `
                    <div class="project-files-empty">
                        <i class="fi ${emptyConfig.emptyIcon}"></i>
                        <span>${emptyConfig.emptyText}</span>
                    </div>
                `;
                return;
            }

            // Collect all image entries for lightbox (with protected flag)
            const gridImages = files
                .filter(file => {
                    const filePath = file.file_path || file.path || '';
                    return /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(filePath);
                })
                .map(file => {
                    const filePath = file.file_path || file.path || '';
                    const cleanPath = filePath.replace(/^\//, '');
                    return { url: `/storage/${cleanPath}`, isProtected: isProtected };
                });

            // Add grid images to global lightbox array
            const startIndex = lightboxImages.length;
            lightboxImages.push(...gridImages);

            grid.innerHTML = files.map((file, idx) => {
                const filePath = file.file_path || file.path || '';
                const fileName = filePath.split('/').pop() || 'Document';
                const fileType = file.file_type || '';

                // Determine if it's an image
                const isImage = /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(filePath);
                const cleanPath = filePath.replace(/^\//, '');
                const url = `/storage/${cleanPath}`;

                // Find this image's index in the lightbox array
                const imageIndex = isImage ? lightboxImages.findIndex(entry => entry.url === url) : -1;
                const protectedClass = isProtected ? ' protected' : '';

                if (isImage) {
                    return `
                        <div class="project-file-preview${protectedClass}" onclick="openLightbox('${url}', ${imageIndex})">
                            <img src="${url}" alt="${fileName}" class="project-file-preview-image"
                                 onerror="this.parentElement.innerHTML='<div class=project-file-preview-icon><i class=\\'fi fi-rr-file-pdf\\'></i><span class=project-file-name>${fileName}</span></div>'">
                            ${fileType ? `<span class="project-file-type-badge">${fileType}</span>` : ''}
                            ${isProtected ? '<div class="document-label"><i class="fi fi-rr-shield-check"></i> Protected Document</div>' : ''}
                        </div>
                    `;
                } else {
                    return `
                        <div class="project-file-preview${protectedClass}" onclick="window.open('${url}', '_blank')">
                            <div class="project-file-preview-icon">
                                <i class="fi fi-rr-file-pdf"></i>
                                <span class="project-file-name">${fileName}</span>
                            </div>
                            ${fileType ? `<span class="project-file-type-badge">${fileType}</span>` : ''}
                            ${isProtected ? '<div class="document-label"><i class="fi fi-rr-shield-check"></i> Protected Document</div>' : ''}
                        </div>
                    `;
                }
            }).join('');
        }
    </script>
@endsection
