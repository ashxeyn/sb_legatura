<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\admin\storeMilestoneRequest;
use App\Http\Requests\admin\updateMilestoneRequest;
use App\Models\admin\milestone;
use Illuminate\Http\Request;
use App\Services\AdminActivityLog;

class milestoneController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int)$request->input('per_page',15);
        $q = milestone::query();
        if ($project = $request->input('project_id')) {
            $q->where('project_id',$project);
        }
        return response()->json($q->orderBy('milestone_id','desc')->paginate($perPage));
    }

    public function show($id)
    {
        $m = milestone::find($id);
        if (!$m) return response()->json(['error'=>'Not found'],404);
        return response()->json($m);
    }

    public function store(storeMilestoneRequest $request)
    {
        $m = milestone::create($request->validated());
        AdminActivityLog::log('milestone_created', ['milestone_id' => $m->milestone_id ?? $m->id, 'project_id' => $m->project_id]);
        return response()->json($m,201);
    }

    public function update(updateMilestoneRequest $request, $id)
    {
        $m = milestone::find($id);
        if (!$m) return response()->json(['error'=>'Not found'],404);
        $m->update($request->validated());
        AdminActivityLog::log('milestone_updated', ['milestone_id' => $id]);
        return response()->json($m);
    }

    public function destroy($id)
    {
        $m = milestone::find($id);
        if (!$m) return response()->json(['error'=>'Not found'],404);
        $m->delete();
        AdminActivityLog::log('milestone_deleted', ['milestone_id' => $id]);
        return response()->json(['deleted'=>true]);
    }
}
