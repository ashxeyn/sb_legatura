<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\admin\storeProjectRequest;
use App\Http\Requests\admin\updateProjectRequest;
use App\Models\admin\projectClass;
use Illuminate\Http\Request;

class projectController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        $q = projectClass::query();

        if ($search = $request->input('search')) {
            $q->where('project_title', 'like', "%{$search}%");
        }

        $projects = $q->orderBy('project_id', 'desc')->paginate($perPage);
        return response()->json($projects);
    }

    public function show($id)
    {
        $project = projectClass::find($id);
        if (!$project) return response()->json(['error' => 'Not found'], 404);
        return response()->json($project);
    }

    public function store(storeProjectRequest $request)
    {
        $data = $request->validated();
        $project = projectClass::create($data);
        return response()->json($project, 201);
    }

    public function update(updateProjectRequest $request, $id)
    {
        $project = projectClass::find($id);
        if (!$project) return response()->json(['error' => 'Not found'], 404);
        $project->update($request->validated());
        return response()->json($project);
    }

    public function destroy($id)
    {
        $project = projectClass::find($id);
        if (!$project) return response()->json(['error' => 'Not found'], 404);
        $project->delete();
        return response()->json(['deleted' => true]);
    }
}
