<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\admin\storeProjectRequest;
use App\Http\Requests\admin\updateProjectRequest;
use App\Models\admin\project;
use Illuminate\Http\Request;
use App\Services\AdminActivityLog;

class projectController extends Controller
{
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
        $data = $request->validated();
        $project = project::create($data);
        AdminActivityLog::log('project_created', ['project_id' => $project->project_id ?? $project->id]);
        return response()->json($project, 201);
    }

    public function update(updateProjectRequest $request, $id)
    {
        $project = project::find($id);
        if (!$project) return response()->json(['error' => 'Not found'], 404);
        $project->update($request->validated());
        AdminActivityLog::log('project_updated', ['project_id' => $id]);
        return response()->json($project);
    }

    public function destroy($id)
    {
        $project = project::find($id);
        if (!$project) return response()->json(['error' => 'Not found'], 404);
        $project->delete();
        AdminActivityLog::log('project_deleted', ['project_id' => $id]);
        return response()->json(['deleted' => true]);
    }
}
