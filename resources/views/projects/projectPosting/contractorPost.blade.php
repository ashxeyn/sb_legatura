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
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }

        .header .status {
            display: inline-block;
            padding: 5px 15px;
            background-color: #ff6b00;
            color: white;
            border-radius: 20px;
            font-size: 14px;
            text-transform: uppercase;
        }

        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .media-section {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .main-media {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .media-gallery {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .media-gallery img,
        .media-gallery video {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .media-gallery img:hover,
        .media-gallery video:hover {
            transform: scale(1.05);
        }

        .details-section {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .details-section h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #ff6b00;
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

        .description-section {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .description-section h2 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #ff6b00;
            padding-bottom: 10px;
        }

        .description {
            line-height: 1.6;
            color: #666;
        }

        .no-media {
            width: 100%;
            height: 400px;
            background-color: #e0e0e0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 18px;
        }

        .actions {
            margin-top: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: #ff6b00;
            color: white;
        }

        .btn-primary:hover {
            background-color: #e55a00;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
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
            <div class="media-section">
                @php
                    $mediaFiles = $project->files->filter(function($file) {
                        return str_contains($file->file_path, 'contractor_media');
                    });
                    $mainMedia = $mediaFiles->first();
                @endphp

                @if($mainMedia)
                    @if(in_array(strtolower(pathinfo($mainMedia->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif']))
                        <img src="{{ asset('storage/' . $mainMedia->file_path) }}" alt="Media" class="main-media" id="mainMedia">
                    @else
                        <video src="{{ asset('storage/' . $mainMedia->file_path) }}" controls class="main-media" id="mainMedia"></video>
                    @endif
                    @if($mediaFiles->count() > 1)
                        <div class="media-gallery">
                            @foreach($mediaFiles as $media)
                                @if(in_array(strtolower(pathinfo($media->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif']))
                                    <img src="{{ asset('storage/' . $media->file_path) }}" alt="Media" onclick="changeMainMedia('{{ asset('storage/' . $media->file_path) }}', 'image')">
                                @else
                                    <video src="{{ asset('storage/' . $media->file_path) }}" onclick="changeMainMedia('{{ asset('storage/' . $media->file_path) }}', 'video')"></video>
                                @endif
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="no-media">No media available</div>
                @endif
            </div>

            <div class="details-section">
                <h2>Project Details</h2>
                
                @if($project->project_location)
                <div class="detail-item">
                    <div class="detail-label">Location</div>
                    <div class="detail-value">{{ $project->project_location }}</div>
                </div>
                @endif

                @if($project->property_type)
                <div class="detail-item">
                    <div class="detail-label">Property Type</div>
                    <div class="detail-value">{{ $project->property_type }}</div>
                </div>
                @endif

                @if($project->contractorType)
                <div class="detail-item">
                    <div class="detail-label">Contractor Type</div>
                    <div class="detail-value">{{ $project->contractorType->type_name }}</div>
                </div>
                @endif

                <div class="detail-item">
                    <div class="detail-label">Posted On</div>
                    <div class="detail-value">{{ \Carbon\Carbon::parse($project->created_at)->format('F d, Y') }}</div>
                </div>
            </div>
        </div>

        <div class="description-section">
            <h2>Description</h2>
            <div class="description">{{ $project->project_description }}</div>
        </div>

        <div class="actions">
            <a href="{{ route('contractorProjects.create') }}" class="btn btn-primary">Post Another</a>
        </div>
    </div>

    <script>
        function changeMainMedia(mediaSrc, type) {
            const mainMedia = document.getElementById('mainMedia');
            if (type === 'image') {
                mainMedia.src = mediaSrc;
                mainMedia.tagName = 'img';
            } else {
                mainMedia.src = mediaSrc;
                mainMedia.load();
            }
        }
    </script>
</body>
</html>