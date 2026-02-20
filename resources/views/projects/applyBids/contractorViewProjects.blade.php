<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Projects - Contractor</title>
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
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
        }

        .page-header h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }

        .projects-feed {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .project-post {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .project-post:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .post-content {
            padding: 20px;
        }

        .post-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .project-image-container {
            width: 100%;
            margin-bottom: 15px;
            border-radius: 8px;
            overflow: hidden;
            background-color: #e0e0e0;
        }

        .project-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            display: block;
        }

        .no-image {
            width: 100%;
            height: 400px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .post-timestamp {
            color: #65676b;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .view-details-btn {
            background-color: #1877f2;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            text-align: center;
        }

        .view-details-btn:hover {
            background-color: #1565c0;
            transform: scale(1.02);
        }

        .view-details-btn:active {
            transform: scale(0.98);
        }

        .no-projects {
            text-align: center;
            padding: 60px 20px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .no-projects h2 {
            color: #65676b;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .no-projects p {
            color: #8a8d91;
            font-size: 16px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .page-header h1 {
                font-size: 24px;
            }

            .project-image {
                height: 300px;
            }

            .no-image {
                height: 300px;
            }

            .post-title {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Browse Projects</h1>
            <p style="color: #65676b;">Find projects posted by property owners</p>
        </div>

        @if(session('success'))
            <div style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <strong>Success!</strong> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <strong>Error!</strong> {{ session('error') }}
            </div>
        @endif

        <div class="projects-feed">
            @if($projects && $projects->count() > 0)
                @foreach($projects as $project)
                    <div class="project-post">
                        <div class="post-content">
                            <h2 class="post-title">{{ $project->project_title }}</h2>
                            
                            <div class="project-image-container">
                                @php
                                    $housePhotos = $project->files->filter(function($file) {
                                        return str_contains($file->file_path, 'house_photos');
                                    });
                                    $mainPhoto = $housePhotos->first();
                                @endphp

                                @if($mainPhoto)
                                    <img src="{{ asset('storage/' . $mainPhoto->file_path) }}" alt="{{ $project->project_title }}" class="project-image">
                                @else
                                    <div class="no-image">No Image Available</div>
                                @endif
                            </div>

                            <div class="post-timestamp">
                                {{ \Carbon\Carbon::parse($project->created_at)->format('g:i A · F j, Y') }}
                            </div>

                            <div style="margin-top: 15px;">
                                <a href="/contractor/projects/{{ $project->project_id }}" class="view-details-btn">View Details</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="no-projects">
                    <h2>No Projects Available</h2>
                    <p>There are currently no open projects posted by property owners.</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>

