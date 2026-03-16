<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\admin\storeProjectRequest;
use App\Http\Requests\admin\updateProjectRequest;
use App\Models\admin\project;
use App\Traits\WithAtomicLock;
use Illuminate\Http\Request;
use App\Services\AdminActivityLog;

class projectController extends Controller
{
    use WithAtomicLock;

    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $q = project::query();

        if ($search = $request->input('search')) {
            $q->where('project_title', 'like', "%{$search}%");
        }

        $projects = $q->orderBy('project_id', 'desc')->paginate($perPage);
        return response()->json($projects);
    }

    public function show($id)
    {
        $project = project::find($id);
        if (!$project) return response()->json(['error' => 'Not found'], 404);
        return response()->json($project);
    }

    public function store(storeProjectRequest $request)
    {
        return $this->withLock("admin_create_project_" . auth()->id(), function () use ($request) {
            $data = $request->validated();
            $project = project::create($data);
            AdminActivityLog::log('project_created', ['project_id' => $project->project_id ?? $project->id]);
            return response()->json($project, 201);
        });
    }

    public function update(updateProjectRequest $request, $id)
    {
        $project = project::find($id);
        if (!$project) return response()->json(['error' => 'Not found'], 404);
        return $this->withLock("admin_update_project_{$id}", function () use ($request, $project, $id) {
            $project->update($request->validated());
            AdminActivityLog::log('project_updated', ['project_id' => $id]);
            return response()->json($project);
        });
    }

    public function destroy($id)
    {
        $project = project::find($id);
        if (!$project) return response()->json(['error' => 'Not found'], 404);
        return $this->withLock("admin_delete_project_{$id}", function () use ($project, $id) {
            $project->delete();
            AdminActivityLog::log('project_deleted', ['project_id' => $id]);
            return response()->json(['deleted' => true]);
        });
    }
}
