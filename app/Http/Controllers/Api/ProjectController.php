<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProjectController extends Controller
{
    public function index(Request $request): ResourceCollection
    {
        $projects = Project::query()
            ->when($request->integer('owner_id'), fn($q, $ownerId) => $q->where('owner_id', $ownerId))
            ->latest('project_id')
            ->paginate(10);

        return ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request): ProjectResource
    {
        $project = Project::create($request->only([
            'owner_id',
            'project_title',
            'project_description',
            'project_location',
            'budget_range_min',
            'budget_range_max',
            'lot_size',
            'property_type',
            'type_id',
            'to_finish',
            'project_status',
            'selected_contractor_id',
            'bidding_deadline',
        ]));

        return new ProjectResource($project);
    }

    public function show(Project $project): ProjectResource
    {
        // Ownership enforcement can be added once auth is mapped to property_owners
        return new ProjectResource($project);
    }

    public function update(UpdateProjectRequest $request, Project $project): ProjectResource
    {
        $project->fill($request->only([
            'owner_id',
            'project_title',
            'project_description',
            'project_location',
            'budget_range_min',
            'budget_range_max',
            'lot_size',
            'property_type',
            'type_id',
            'to_finish',
            'project_status',
            'selected_contractor_id',
            'bidding_deadline',
        ]));
        $project->save();

        return new ProjectResource($project);
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return response()->noContent();
    }

    // Note: once auth is tied to property_owners.user_id, enforce owner-based access here
    protected function authorizeProject(Project $project): void {}
}


