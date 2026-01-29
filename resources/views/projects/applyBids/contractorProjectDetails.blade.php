<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project->project_title }} - Project Details</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }

        .header .status {
            display: inline-block;
            padding: 5px 15px;
            background-color: #5cb85c;
            color: white;
            border-radius: 20px;
            font-size: 14px;
            text-transform: uppercase;
        }

        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .photo-section {
            background-color: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .main-photo {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .photo-gallery img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .photo-gallery img:hover {
            transform: scale(1.05);
        }

        .details-section {
            background-color: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .details-section h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #1877f2;
            padding-bottom: 10px;
        }

        .detail-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: bold;
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 16px;
            color: #333;
        }

        .budget-range {
            font-size: 18px;
            color: #1877f2;
            font-weight: bold;
        }

        .description-section {
            background-color: #fff;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .description-section h2 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #1877f2;
            padding-bottom: 10px;
        }

        .description {
            line-height: 1.6;
            color: #666;
        }

        .no-photo {
            width: 100%;
            height: 400px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .actions {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .btn {
            display: inline-block;
            padding: 14px 40px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.2s, transform 0.1s;
            border: none;
            cursor: pointer;
        }

        .btn-apply-bid {
            background-color: #ff6b35;
            color: white;
            font-size: 18px;
        }

        .btn-apply-bid:hover {
            background-color: #e55a2b;
            transform: scale(1.02);
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $project->project_title }}</h1>
            <span class="status">{{ $project->project_status }}</span>
        </div>

        <div class="main-content">
            <div class="photo-section">
                @php
                    $housePhotos = $project->files->filter(function($file) {
                        return str_contains($file->file_path, 'house_photos');
                    });
                    $mainPhoto = $housePhotos->first();
                @endphp

                @if($mainPhoto)
                    <img src="{{ asset('storage/' . $mainPhoto->file_path) }}" alt="Property Photo" class="main-photo" id="mainPhoto">
                    @if($housePhotos->count() > 1)
                        <div class="photo-gallery">
                            @foreach($housePhotos as $photo)
                                <img src="{{ asset('storage/' . $photo->file_path) }}" alt="Property Photo" onclick="changeMainPhoto('{{ asset('storage/' . $photo->file_path) }}')">
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="no-photo">No photos available</div>
                @endif
            </div>

            <div class="details-section">
                <h2>Project Details</h2>
                
                <div class="detail-item">
                    <div class="detail-label">Location</div>
                    <div class="detail-value">{{ $project->project_location }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Property Type</div>
                    <div class="detail-value">{{ $project->property_type }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Lot Size</div>
                    <div class="detail-value">{{ number_format($project->lot_size) }} sqm</div>
                </div>

                @if($project->floor_area)
                <div class="detail-item">
                    <div class="detail-label">Floor Area</div>
                    <div class="detail-value">{{ number_format($project->floor_area) }} sqm</div>
                </div>
                @endif

                <div class="detail-item">
                    <div class="detail-label">Contractor Type</div>
                    <div class="detail-value">
                        @if($project->type_id == 9 && !empty($project->contractor_type_other))
                            {{ $project->contractorType->type_name ?? 'Others' }} - {{ $project->contractor_type_other }}
                        @else
                            {{ $project->contractorType->type_name ?? 'N/A' }}
                        @endif
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Timeline</div>
                    <div class="detail-value">{{ $project->to_finish ?? 'N/A' }} months</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Budget Range</div>
                    <div class="detail-value budget-range">
                        ₱{{ number_format($project->budget_range_min, 2) }} - ₱{{ number_format($project->budget_range_max, 2) }}
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Bidding Deadline</div>
                    <div class="detail-value">{{ \Carbon\Carbon::parse($project->bidding_deadline)->format('F d, Y h:i A') }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Posted On</div>
                    <div class="detail-value">{{ \Carbon\Carbon::parse($project->created_at)->format('F d, Y') }}</div>
                </div>

                @php
                    $blueprintFile = $project->files->firstWhere('file_type', 'blueprint');
                    $landTitleFile = $project->files->firstWhere(function($file) {
                        return str_contains($file->file_path, 'land_titles');
                    });
                    $supportingDocuments = $project->files->filter(function($file) {
                        return str_contains($file->file_path, 'supporting_documents');
                    });
                @endphp
                @if($blueprintFile)
                <div class="detail-item">
                    <div class="detail-label">Blueprint</div>
                    <div class="detail-value">
                        <a href="{{ asset('storage/' . $blueprintFile->file_path) }}" target="_blank" style="color: #1877f2; text-decoration: underline;">
                            View Blueprint
                        </a>
                    </div>
                </div>
                @endif
                @if($landTitleFile)
                <div class="detail-item">
                    <div class="detail-label">Land Title</div>
                    <div class="detail-value">
                        <a href="{{ asset('storage/' . $landTitleFile->file_path) }}" target="_blank" style="color: #1877f2; text-decoration: underline;">
                            View Land Title
                        </a>
                    </div>
                </div>
                @endif
                @if($supportingDocuments->count() > 0)
                <div class="detail-item">
                    <div class="detail-label">Supporting Documents</div>
                    <div class="detail-value">
                        @foreach($supportingDocuments as $doc)
                            <div style="margin-bottom: 5px;">
                                <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" style="color: #1877f2; text-decoration: underline;">
                                    {{ basename($doc->file_path) }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="description-section">
            <h2>Project Description</h2>
            <div class="description">{{ $project->project_description }}</div>
        </div>

        <div class="actions">
            <a href="{{ route('contractor.browse.projects') }}" class="btn btn-back">← Back to Browse Projects</a>
            <button type="button" class="btn btn-apply-bid" onclick="applyBid({{ $project->project_id }})">Apply Bid</button>
        </div>
    </div>

    <script>
        function changeMainPhoto(photoSrc) {
            document.getElementById('mainPhoto').src = photoSrc;
        }

        function applyBid(projectId) {
            window.location.href = '{{ route("contractor.bid.form", ":id") }}'.replace(':id', projectId);
        }
    </script>
</body>
</html>

