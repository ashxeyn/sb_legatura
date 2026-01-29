<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Projects - Legatura</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <style>
        .header {
            margin-bottom: 20px;
        }

        .project-item {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid #e4e6eb;
            cursor: pointer;
            transition: all 0.2s;
        }

        .project-item:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .project-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .project-item-header strong {
            font-size: 18px;
            color: #1c1e21;
        }

        .project-item p {
            color: #65676b;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .project-item small {
            color: #8a8d91;
            font-size: 12px;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #000;
        }

        .btn-warning:hover {
            background-color: #e0a800;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>My Projects</h1>
        <div class="nav-links">
                <a href="/dashboard">Dashboard</a>
            <a href="/both/disputes">Disputes</a>
                @if(isset($isContractor) && $isContractor)
                    <a href="/contractor/milestone/setup">Milestone Setup</a>
                @endif
            </div>
        </div>

        <div class="card">
            <p style="margin-bottom: 20px; color: #65676b;">Click on a project to view details, progress reports, and payment validations.</p>

        <div id="projectList">
            @if(count($projects) > 0)
                @foreach($projects as $project)
                    <div class="project-item" onclick="window.location.href='/both/projects/{{ $project->project_id }}'">
                            <div class="project-item-header">
                            <strong>{{ $project->project_title }}</strong>
                                @if(isset($isContractor) && $isContractor && isset($project->display_status) && $project->display_status === 'bidded' && isset($project->milestone_status))
                                    @if($project->milestone_status === 'set_up')
                                        <span class="status-badge status-milestone-setup">Milestone Done</span>
                                    @elseif($project->milestone_status === 'not_set_up')
                                        <span class="status-badge status-milestone-not-setup">Milestone Not Set Up Yet</span>
                                    @else
                                        <span class="status-badge status-{{ $project->display_status ?? $project->project_status }}">
                                            {{ ucfirst(str_replace('_', ' ', $project->display_status ?? $project->project_status)) }}
                                        </span>
                                    @endif
                                @else
                                    <span class="status-badge status-{{ $project->display_status ?? $project->project_status }}">
                                {{ ucfirst(str_replace('_', ' ', $project->display_status ?? $project->project_status)) }}
                            </span>
                                @endif
                        </div>
                        <p><strong>Description:</strong> {{ \Illuminate\Support\Str::limit($project->project_description, 150) }}</p>
                        <p><small>Created: {{ date('M d, Y', strtotime($project->created_at)) }}</small></p>

                        @if(isset($project->display_status) && $project->display_status !== 'bidded')
                            <div style="display: flex; gap: 10px; margin-top: 15px;">
                                <button class="btn btn-warning btn-sm" onclick="event.stopPropagation(); editProject({{ $project->project_id }})" style="flex: 1;">
                                    Edit
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="event.stopPropagation(); deleteProject({{ $project->project_id }}, '{{ addslashes($project->project_title) }}')" style="flex: 1;">
                                    Delete
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <h3>No Projects Found</h3>
                    <p>You don't have any projects yet.</p>
                </div>
            @endif
            </div>
        </div>
    </div>

    @include('modals.editPostedProject')
    @include('modals.deletePostedProject')

    <script src="{{ asset('js/modal.js') }}"></script>
    <script>
        // CSRF token helper function
        function getCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        function editProject(projectId) {
            if (typeof ProjectEdit !== 'undefined') {
                ProjectEdit.open(projectId);
            } else {
                alert('Edit functionality not available');
            }
        }

        function deleteProject(projectId, projectTitle) {
            if (typeof ProjectDelete !== 'undefined') {
                ProjectDelete.open(projectId, projectTitle);
            } else {
                alert('Delete functionality not available');
            }
        }
    </script>
</body>
</html>
